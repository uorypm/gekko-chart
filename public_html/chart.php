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

$chartData = [];

$ticketNumber = 0;

// region Инициализация документа
$document = new DOMDocument();
$document->loadHTMLFile($_FILES['file']['tmp_name']);
// endregion

// region Парсинг строк таблицы
$tableRows = $document->getElementsByTagName('tr');
// endregion

// region Находим стартовый баланс
$balance = 0;
// endregion

// region Первая точка со значеним исходного баланса
$chartData[] = [
    'ticket'    => 0,
    'number'    => $ticketNumber,
    'balance'   => $balance,
];
// region

if ($tableRows->length === 0) {
    die('Нет данных для обработки');
}

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
    $ticketId = strval($cellTicket->nodeValue);

    if (strlen($ticketId) < 8 || !ctype_digit($ticketId)) {
        continue;
    }
    // endregion

    /**
     * @var DOMElement $cellProfit Ячейка со значением профита
     */
    $cellProfit = $rowCells->item($rowCells->length - 1);

    $profit = str_replace(' ', '', $cellProfit->nodeValue);

    if (is_numeric($profit)) {
        // region Подсчёт баланса
        $balance += floatval($profit);

        /**
         * @var DOMElement $cellTicketType Ячейка с типом тикета (транзакции)
         */
        $cellTicketType = $rowCells->item(2);

        if ($cellTicketType->nodeValue !== 'balance') {
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

            $commission = str_replace(' ', '', $cellCommission->nodeValue);
            $tax        = str_replace(' ', '', $cellTax->nodeValue);
            $swap       = str_replace(' ', '', $cellSwap->nodeValue);

            $balance += floatval($commission)
                      + floatval($tax)
                      + floatval($swap)
            ;
        }

        $chartData[] = [
            'ticket'    => intval($cellTicket->nodeValue),
            'number'    => $ticketNumber,
            'balance'   => round($balance, 2),
            // По желанию можно ещё данные из таблицы парсить типа даты и т.п.
        ];

        $ticketNumber++;
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
