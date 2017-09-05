<?php
/**
 * В файле опущены некоторые проверки и генерация ошибок
 * Упрощён DataMapper
 * Можно было всё сделать с помощью фабрики, фабричного метода
 * И т.д. и т.п.
 */

/**
 * @var DOMDocument $document       Исходный html-документ с данными
 * @var DOMNodeList $tableRows      Строки таблицы с данными из $document
 * @var DOMElement  $tableRow       Строка таблицы из списка $tableRows
 * @var array       $chartData      Данные для графика
 * @var float       $balance        Баланс счёта
 * @var int         $ticketNumber   Номер тикета по порядку (для графика)
 */

// region Инициализация документа
$document = new DOMDocument();
$document->loadHTMLFile($_FILES['file']['tmp_name']);
// endregion

// region Парсинг строк таблицы
$tableRows = $document->getElementsByTagName('tr');
// endregion

$chartData = [];
$ticketNumber = 0;

// region Находим стартовый баланс
$domXPath = new DOMXPath($document);

$xQuery = ".//*[contains(text(), 'Deposit/Withdrawal')]/../following-sibling::*[1]";

$balance = floatval($domXPath->query($xQuery)->item(0)->nodeValue);

$chartData[] = [
    'ticket'    => 0,
    'number'    => $ticketNumber,
    'balance'   => $balance,
];
// endregion

// region Генерация данных
foreach ($tableRows as $tableRow) {
    /**
     * @var DOMNodeList $rowCells Ячейки из основной таблицы с данными
     */
    $rowCells = $tableRow->getElementsByTagName('td');

    /**
     * @var DOMElement $cellTicket Ячейка с номером тикета (транзакции)
     */
    $cellTicket = $rowCells->item(0);

    // region Проверяем, что в строке действительно содержится
    //        информация о тикете (транзакции), иначе пропускаем итерацию
    if (!$cellTicket->hasAttribute('title')) {
        continue;
    }

    if (!ctype_digit($cellTicket->nodeValue)) {
        continue;
    }
    // endregion

    /**
     * @var DOMElement $cellProfit Ячейка со значением профита
     */
    $cellProfit = $rowCells->item($rowCells->length - 1);

    if (is_numeric($cellProfit->nodeValue)) {
        /**
         * @var DOMElement $cellCommission Ячейка со значением комиссии
         */
        $cellCommission = $rowCells->item($rowCells->length - 4);

        /**
         * @var DOMElement $cellTax Ячейка со значением таксы
         */
        $cellTax = $rowCells->item($rowCells->length - 3);

        /**
         * @var DOMElement $cellSwap Ячейка со значением свопа
         */
        $cellSwap = $rowCells->item($rowCells->length - 2);

        /**
         * @var DOMElement $cellTicketType Ячейка с типом тикета (транзакции)
         */
        $cellTicketType = $rowCells->item(2);

        // region Подсчёт баланса (Баланс = Профит + Своп + Такса + Комиссия)
        /**
         * Как я понял, тип тикета (транзакции) 'balance' не влияет на баланс
         * (это что-то вроде инфо-поля)
         */
        if ($cellTicketType->nodeValue !== 'balance') {
            $balance += floatval($cellProfit->nodeValue)
                      + floatval($cellCommission->nodeValue)
                      + floatval($cellTax->nodeValue)
                      + floatval($cellSwap->nodeValue)
            ;

            /**
             * Как понял из исходной таблицы данные
             * для графика сохранять именно здесь
             */
            $chartData[] = [
                'ticket'    => intval($cellTicket->nodeValue),
                'number'    => $ticketNumber,
                'balance'   => $balance,
                // По желанию можно ещё данные из таблицы парсить типа даты и т.п.
            ];

            $ticketNumber++;
        }
        // endregion
    }
}
// endregion

// region Что-то типа DataMapper'а
$data = [];

foreach ($chartData as $chartItem) {
    $data[] = [
        'x'         => $chartItem['number'],
        'y'         => $chartItem['balance'],
        'ticket'    => $chartItem['ticket'],
    ];
}
// endregion

// region Подключаем шалон страницы
include 'chart.tpl.php';
// endregion
