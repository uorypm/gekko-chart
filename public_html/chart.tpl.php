<?php
/**
 * @var array $data Данные для графика
 */

?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Chart</title>
</head>
<body>
<div id="chartContainer"
     style="
             height: 100%;
             margin: 0 auto;
             width: 100%;
         "
></div>
<script type="text/javascript" src="/js/canvasjs.min.js"></script>
<script type="text/javascript">
    window.onload = function () {
        var chart = new CanvasJS.Chart("chartContainer", {
            toolTip: {
                shared: true,
                content: 'Ticket #{ticket}<br/>Balance {y}'
            },
            data: [
                {
                    type: "line",
                    dataPoints: <?=json_encode($data, JSON_PRETTY_PRINT);?>
                }
            ]
        });

        chart.render();
    }
</script>
</body>
</html>