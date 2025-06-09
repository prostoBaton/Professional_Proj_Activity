<?php

    $conn = new mysqli("localhost", "root", "", "TIT");
    if ($conn->connect_error) {
        die("Оно померло: " . $conn->connect_error);
    } 
    $prikol = "GOIDA";



    function otkl($a, $srz){
        $x = array(); 
        for($i = 0; $i < count($a); $i++){
            $x[] = ($a[$i] - $srz) ** 2;
        }
        return $x;
    }

    function otklVer2($a1, $srz1, $a2, $srz2){
        $x = array(); 
        for($i = 0; $i < count($a1); $i++){
            $x[] = ($srz1 - $a1[$i]) * ($srz2 - $a2[$i]);
        }
        return $x;
    }


    


    function Correlation($a1, $a2){  
        $res = 0;      
        // 1
        $sm1 = array_sum($a1);
        $sm2 = array_sum($a2);

        // 2
        $srAr1 = array_sum($a1) / count($a1);
        $srAr2 = array_sum($a2) / count($a2);

        // // 3 4 5
        $otk1 = array_sum(otkl($a1, $srAr1));
        $otk2 = array_sum(otkl($a2, $srAr2));
        //echo $srAr1;
        // // 6

        $otkKV = array_sum(otklVer2($a1, $srAr1, $a2, $srAr2));

        $res = $otkKV/sqrt($otk1 * $otk2);
        return $res;
    
    }

    // 
    $all = Array();
    $result = $conn->query("SELECT * FROM titanic");
    foreach ($result as $row) {
        $all["age"][] = $row["Age"];
        $all["sur"][] = $row["Survived"];
        if ($row["Sex"] == "male"){
            $all["sex"][] = 1.0;
        }
        else{
            $all["sex"][] = 0.0;
            //echo "YEEAH";
        }
        $all["fare"][] = $row["Fare"];
        $all["pclass"][] = $row["Pclass"];
        $all["sib"][] = $row["Siblings/Spouses Aboard"];
        $all["par"][] = $row["Parents/Children Aboard"];
    }
    //var_dump($all["sex"]);
    //$p = stats_stat_correlation($all["age"], $all["pclass"]);
    
    //echo Correlation($all["sex"], $all["pclass"]);
    //echo $p;
    // Получение возраста
    $result = $conn->query("SELECT Age FROM titanic");
    $age = array(7);
    foreach ($result as $row) {
        $k = $row["Age"];
        switch (true) {
            case ($k <= 12):
                $age[0] += 1;
                break;
            case (($k > 12) && ($k <= 18)):
                $age[1] += 1;
                break;
            case (($k > 18) && ($k <= 26)):
                $age[2] += 1;
                break;
            case (($k > 26) && ($k <= 35)):
                $age[3] += 1;
                break;
            case (($k > 36) && ($k <= 50)):
                $age[4] += 1;
                break; 
            case (($k > 50) && ($k <= 65)):
                $age[5] += 1;
                break;
            case ($k >= 65):
                $age[6] += 1;
                break;
        }
     }
    //----

    $i1 = 0;

    // ДЛЯ ТАБЛИЦЫ
            
    $kol_pas1 = 0;
    $sum1 = 0;
    $live1 = 0;
    $die1 = 0;

    // ----

    $result = $conn->query("SELECT * FROM titanic WHERE Pclass = '1'");
    foreach ($result as $row) {
        $i1 += 1;
        $age_c1[] = $row["Age"];

        $kol_pas1 += 1;
        $sum1 += $row["Fare"];
        if ($row["Survived"] == 1){
            $live1 += 1;
        }
        if ($row["Survived"] == 0){
            $die1 += 1;
        }
    }
    sort($age_c1);

    // Медиана
    $m1 = (1/2)*($i1 + 1);
    if (is_int($m1) == false){
        $m1 = ($age_c1[round($m1, 0, PHP_ROUND_HALF_UP)] + $age_c1[round($m1, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $m1 = $age_c1[$m1];
    }
    // -------------

    // Первый квартиль
    $q1_1 = (3/4)*($i1 + 1);
    if (is_int($q1_1) == false){
        $q1_1 = ($age_c1[round($q1_1, 0, PHP_ROUND_HALF_UP)] + $age_c1[round($q1_1, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q1_1 = $age_c1[$q1_1];
    }
    // -------

    // Второй квартиль
    $q2_1 = (1/4)*($i1 + 1);
    if (is_int($q2_1) == false){
        $q2_1 = ($age_c1[round($q2_1, 0, PHP_ROUND_HALF_UP)] + $age_c1[round($q2_1, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q2_1 = $age_c1[$q2_1];
    }
    // ----------
    
    


    $i2 = 0;

    // ДЛЯ ТАБЛИЦЫ
        
    $kol_pas2 = 0;
    $sum2 = 0;
    $live2 = 0;
    $die2 = 0;

    // ----


    $result = $conn->query("SELECT * FROM titanic WHERE Pclass = '2'");
    foreach ($result as $row) {
        $i2 += 1;
        $age_c2[] = $row["Age"];

        $kol_pas2 += 1;
        $sum2 += $row["Fare"];
        if ($row["Survived"] == 1){
            $live2 += 1;
        }
        if ($row["Survived"] == 0){
            $die2 += 1;
        }
    }
    sort($age_c2);
    
    // Медиана
    $m2 = (1/2)*($i2 + 1);
    if (is_int($m2) == false){
        $m2 = ($age_c2[round($m2, 0, PHP_ROUND_HALF_UP)] + $age_c2[round($m2, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $m2 = $age_c2[$m2];
    }
    // ------

    // Первый квартиль
    $q1_2 = (3/4)*($i2 + 1);
    if (is_int($q1_2) == false){
        $q1_2 = ($age_c2[round($q1_2, 0, PHP_ROUND_HALF_UP)] + $age_c2[round($q1_2, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q1_2 = $age_c2[$q1_2];
    }

    // ------

    // Второй квартиль
    $q2_2 = (1/4)*($i2 + 1);
    if (is_int($q2_2) == false){
        $q2_2 = ($age_c2[round($q2_2, 0, PHP_ROUND_HALF_UP)] + $age_c2[round($q2_2, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q2_2 = $age_c2[$q2_2];
    }
    // --------


    $i3 = 0;

    // ДЛЯ ТАБЛИЦЫ
    
    $kol_pas3 = 0;
    $sum3 = 0;
    $live3 = 0;
    $die3 = 0;

    // ----

    $result = $conn->query("SELECT * FROM titanic WHERE Pclass = '3'");
    foreach ($result as $row) {
        $i3 += 1;
        $age_c3[] = $row["Age"];

        $kol_pas3 += 1;
        $sum3 += $row["Fare"];
        if ($row["Survived"] == 1){
            $live3 += 1;
        }
        if ($row["Survived"] == 0){
            $die3 += 1;
        }
    }
    sort($age_c3);

    // Медиана
    $m3 = (1/2)*($i3 + 1);
    if (is_int($m3) == false){
        $m3 = ($age_c3[round($m3, 0, PHP_ROUND_HALF_UP)] + $age_c3[round($m3, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $m3 = $age_c3[$m3];
    }
    // ---------

    // Первый квартиль
    $q1_3 = (3/4)*($i3 + 1);
    if (is_int($q1_3) == false){
        $q1_3 = ($age_c3[round($q1_3, 0, PHP_ROUND_HALF_UP)] + $age_c3[round($q1_3, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q1_3 = $age_c3[$q1_3];
    }
    // ---------

    // Второй квартиль
    $q2_3 = (1/4)*($i3 + 1);
    if (is_int($q2_3) == false){
        $q2_3 = ($age_c3[round($q2_3, 0, PHP_ROUND_HALF_UP)] + $age_c3[round($q2_3, 0, PHP_ROUND_HALF_DOWN)])/2;
    }
    else{
        $q2_3 = $age_c3[$q2_3];
    }
    // --------




    $result = $conn->query("SELECT COUNT(*) FROM titanic WHERE Survived = '1' AND Sex='female'");
    $live_w = mysqli_fetch_assoc($result);
    $result = $conn->query("SELECT COUNT(*) FROM titanic WHERE Survived = '0' AND Sex='female'");
    $die_w = mysqli_fetch_assoc($result);

    $result = $conn->query("SELECT COUNT(*) FROM titanic WHERE Survived = '1' AND Sex='male'");
    $live_m = mysqli_fetch_assoc($result);
    $result = $conn->query("SELECT COUNT(*) FROM titanic WHERE Survived = '0' AND Sex='male'");
    $die_m = mysqli_fetch_assoc($result);


    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '1' AND Sex='male'");
    $k = 0;
    $cm1 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cm1 += $row["Fare"];
            $k += 1;
        }
    }
    $cm1 = $cm1 / $k;

    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '1' AND Sex='female'");
    $k = 0;
    $cf1 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cf1 += $row["Fare"];
            $k += 1;
        }
    }
    $cf1 = $cf1 / $k;

    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '2' AND Sex='male'");
    $k = 0;
    $cm2 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cm2 += $row["Fare"];
            $k += 1;
        }
    }
    $cm2 = $cm2 / $k;

    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '2' AND Sex='female'");
    $k = 0;
    $cf2 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cf2 += $row["Fare"];
            $k += 1;
        }
    }
    $cf2 = $cf2 / $k;

    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '3' AND Sex='male'");
    $k = 0;
    $cm3 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cm3 += $row["Fare"];
            $k += 1;
        }
    }
    $cm3 = $cm3 / $k;

    $result = $conn->query("SELECT Fare FROM titanic WHERE Pclass = '3' AND Sex='female'");
    $k = 0;
    $cf3 = 0;
    foreach ($result as $row) {
        if ($row["Fare"] != 0){
            $cf3 += $row["Fare"];
            $k += 1;
        }
    }
    $cf3 = $cf3 / $k;
     
    $kf = 0;
    $sf = 0;
    $result = $conn->query("SELECT * FROM titanic WHERE Sex='female'");
    foreach ($result as $row) {
        $kf += 1;
        $sf += $row["Fare"];
    }

    $km = 0;
    $sm = 0;
    $result = $conn->query("SELECT * FROM titanic WHERE Sex='male'");
    foreach ($result as $row) {
        $km += 1;
        $sm += $row["Fare"];
    }



    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ТИТАНИК ОБРАБОТКА ИНФОРМАЦИИ ЕХЕХЕ</title>
    <script src="https://cdn.anychart.com/releases/8.13.0/js/anychart-base.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<script>
  anychart.onDocumentReady(function () {
        data = [
            ["0 - 12", <? echo $age[0];?>],
            ["13 - 18", <? echo $age[1];?>],
            ["19 - 26", <? echo $age[2];?>],
            ["27 - 35", <? echo $age[3];?>],
            ["36 - 50", <? echo $age[4];?>],
            ["50 - 65", <? echo $age[5];?>],
            ["65+", <? echo $age[6];?>]
        ];
        var dataSet = anychart.data.set(data);
        var mapping = dataSet.mapAs({x: 0, value: 1});
        var chart = anychart.column();
        var series = chart.column(mapping);
        chart.title("Распределение возраста пассажиров на корабле");
        chart.container("container");
            chart.draw();
        });

        anychart.onDocumentReady(function () {

            var data = [
            {x: "Первый класс", low: <?echo $age_c1[0];?>, q1: <?echo $q2_1;?>, median: <?echo $m1;?>, q3: <?echo $q1_1;?>, high: <?echo $age_c1[$i1 - 1];?>},
            {x: "Второй класс", low: <?echo $age_c2[0];?>, q1: <?echo $q2_2;?>, median: <?echo $m2;?>, q3: <?echo $q1_2;?>, high: <?echo $age_c2[$i2 - 1];?>},
            {x: "Третий класс", low: <?echo $age_c2[0];?>, q1: <?echo $q2_3;?>, median: <?echo $m3;?>, q3: <?echo $q1_3;?>, high: <?echo $age_c3[$i3 - 1];?>}
            ];
            var chart = anychart.vertical();
            var series = chart.box(data);
            chart.title("Распределение возрастов в разных классах");
            chart.container("container1");
            chart.draw();
        });



    anychart.onDocumentLoad(function () {
    
        var chart = anychart.pie();
        chart.data([
            ["Выжило", <?echo $live_w["COUNT(*)"];?> ],
            ["Погибло", <?echo $die_w["COUNT(*)"];?>]
        ]);
        chart.title("Смертность женщин");
        chart.container("container2");
        chart.draw();
  });

  anychart.onDocumentLoad(function () {
    
    var chart = anychart.pie();
    chart.data([
        ["Выжило", <?echo $live_m["COUNT(*)"];?> ],
        ["Погибло", <?echo $die_m["COUNT(*)"];?>]
    ]);
    chart.title("Смертность мужчин");
    chart.container("container3");
    chart.draw();
    });


    anychart.onDocumentReady(function () {

        // create a data set
        var data = anychart.data.set([
        ["Первый", <?echo $cf1?>, <?echo $cm1?>],
        ["Второй", <?echo $cf2?>, <?echo $cm2?>],
        ["Третий", <?echo $cf3?>, <?echo $cm3?>]
        ]);

        // map the data
        var seriesData_1 = data.mapAs({x: 0, value: 1, fill: 3, stroke: 5, label: 6});
        var seriesData_2 = data.mapAs({x: 0, value: 2, fill: 4, stroke: 5, label: 6});

        // create a chart
        var chart = anychart.bar();

        var series1 = chart.bar(seriesData_1);
        series1.name("Female");

        var series2 = chart.bar(seriesData_2);
        series2.name("Male");

        chart.title("Средняя стоимость билетов разных классов разделенных по полу");

        var xAxis = chart.xAxis();
        xAxis.title("Класс");
        var yAxis = chart.yAxis();
        yAxis.title("Стоимость, $");

        chart.container("container4");

        chart.draw();
        });



    anychart.onDocumentReady(function () {
      // create data set on our data
      var chartData = {
        title: 'Средняя стоимость билета от класса и пола',
        header: ['#', 'Первый', 'Второй', 'Третий'],
        rows: [
          ['Женщины', <? echo (int)$cf1; ?>, <? echo (int)$cf2; ?>, <? echo (int)$cf3; ?>],
          ['Мужчины', <? echo (int)$cm1; ?>, <? echo (int)$cm2; ?>, <? echo (int)$cm3; ?>]
        ]
      };

      // create column chart
      var chart = anychart.column();

      // set chart data
      chart.data(chartData);

      // turn on chart animation
      chart.animation(true);

      chart.yAxis().labels().format('${%Value}{groupsSeparator: }');

      // set titles for Y-axis
      chart.yAxis().title('Revenue');

      chart
        .labels()
        .enabled(true)
        .position('center-top')
        .anchor('center-bottom')
        .format('${%Value}{groupsSeparator: }');
      chart.hovered().labels(false);

      // turn on legend and tune it
      chart.legend().enabled(true).fontSize(13).padding([0, 0, 20, 0]);

      // interactivity settings and tooltip position
      chart.interactivity().hoverMode('single');

      chart
        .tooltip()
        .positionMode('point')
        .position('center-top')
        .anchor('center-bottom')
        .offsetX(0)
        .offsetY(5)
        .titleFormat('{%X}')
        .format('{%SeriesName} : ${%Value}{groupsSeparator: }');

      // set container id for the chart
      chart.container('container5');

      // initiate chart drawing
      chart.draw();
    });
</script>
<body>
    <div id="container5" style="width: 500px; height: 400px;"></div>
    <div id="container" style="width: 500px; height: 400px;"></div> 
    <div id="container1" style="width: 500px; height: 400px;" ></div>
    <div id="container2" style="width: 500px; height: 400px;" ></div>
    <div id="container3" style="width: 500px; height: 400px;"></div>
    <div id="container4" style="width: 500px; height: 400px;"></div>

    <table class="table">
        <thead>
            <tr class="table-danger">
            <th scope="col">Класс</th>
            <th scope="col">Количество пассажиров</th>
            <th scope="col">Общая стоимость билетов, $</th>
            <th scope="col">Количество выживших</th>
            <th scope="col">Количество погибших</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <th scope="row">Первый</th>
            <td><?echo $kol_pas1 ?></td>
            <td><?echo $sum1 ?></td>
            <td><?echo $live1 ?></td>
            <td><?echo $die1 ?></td>
            </tr>
            <tr>
            <th scope="row">Второй</th>
            <td><?echo $kol_pas2 ?></td>
            <td><?echo $sum2 ?></td>
            <td><?echo $live2 ?></td>
            <td><?echo $die2 ?></td>
            </tr>
            <tr>
            <th scope="row">Третий</th>
            <td><?echo $kol_pas3 ?></td>
            <td><?echo $sum3 ?></td>
            <td><?echo $live3 ?></td>
            <td><?echo $die3 ?></td>
            </tr>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr class="table-primary">
            <th scope="col">Пол</th>
            <th scope="col">Количество пассажиров</th>
            <th scope="col">Общая стоимость билетов, $</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <th scope="row">Женский</th>
            <td><?echo $kf; ?></td>
            <td><?echo $sf; ?></td>
            </tr>
            <tr>
            <th scope="row">Мужской</th>
            <td><?echo $km; ?></td>
            <td><?echo $sm; ?></td>
            </tr>
        </tbody>
    </table>

    <hr>

    <table class="table">
        <thead>
            <tr class="table-success">
            <th scope="col">Пол</th>
            <th scope="col">Количество пассажиров</th>
            <th scope="col">Общая стоимость билетов, $</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <th scope="row">Женский</th>
            <td><?echo $kf; ?></td>
            <td><?echo $sf; ?></td>
            </tr>
            <tr>
            <th scope="row">Мужской</th>
            <td><?echo $km; ?></td>
            <td><?echo $sm; ?></td>
            </tr>
        </tbody>
    </table>
    <hr>
    <table class="table">
        <tbody>
        <tr>
            <th scope="col">/</th>
            <th scope="col">Возраст</th>
            <th scope="col">Стоимость</th>
            <th scope="col">Количество братьев/сестер</th>
            <th scope="col">Количество родителей/дети</th>    
        </tr>
        <tr>
            <th scope="col">Возраст</th>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["age"], $all["age"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["age"], $all["fare"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["age"], $all["sib"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["age"], $all["par"]));?></td>
                
            </tr>
            <tr>
                <th scope="row">Стоимость</th>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["fare"], $all["age"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["fare"], $all["fare"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["fare"], $all["sib"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["fare"], $all["par"]));?></td>
            </tr>
            <tr>
                <th scope="row">Количество братьев/сестер</th>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["sib"], $all["age"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["sib"], $all["fare"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["sib"], $all["sib"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["sib"], $all["par"]));?></td>
            </tr>
            <tr>
                <th scope="row">Количество родителей детей</th>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["par"], $all["age"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["par"], $all["fare"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["par"], $all["sib"]));?></td>
                <td><?echo sprintf("%.2f" . "</br>\n", Correlation($all["par"], $all["par"]));?></td>
            </tr>
        </tbody>
    </table>

    

    
</body>
</html>