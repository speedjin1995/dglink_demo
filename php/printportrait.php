<?php

require_once 'db_connect.php';

$compids = '1';
$compname = 'SYNCTRONIX TECHNOLOGY (M) SDN BHD';
$compaddress = 'No.34, Jalan Bagan 1, Taman Bagan, 13400 Butterworth. Penang. Malaysia.';
$compphone = '6043325822';
$compiemail = 'admin@synctronix.com.my';

$mapOfWeights = array();
$mapOfBirdsToCages = array();

$totalGross = 0.0;
$totalCrate = 0.0;
$totalReduce = 0.0;
$totalNet = 0.0;
$totalCrates = 0;
$totalBirds = 0;
$totalMaleBirds = 0;
$totalMaleCages = 0;
$totalFemaleBirds = 0;
$totalFemaleCages = 0;
$totalMixedBirds = 0;
$totalMixedCages = 0;
 
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

function totalWeight($strings){ 
    $totalSum = 0;

    for ($i =0; $i < count($strings); $i++) {
        if (preg_match('/([\d.]+)/', $strings[$i]['grossWeight'], $matches)) {
            $value = floatval($matches[1]);
            $totalSum += $value;
        }
    }

    return $totalSum;
}

function rearrangeList($weightDetails) {
    global $mapOfWeights, $totalGross, $totalCrate, $totalReduce, $totalNet, $totalCrates, $totalBirds, $totalMaleBirds, $totalMaleCages, $totalFemaleBirds, $totalFemaleCages, $totalMixedBirds, $totalMixedCages, $mapOfBirdsToCages;
    $mapOfWeights = [];
    $mapOfBirdsToCages = [];

    if (!empty($weightDetails)) {
        $array1 = array(); // group
        $array2 = array(); // house
        $array3 = array();

        foreach ($weightDetails as $element) {
            if(!in_array($element['groupNumber'], $array1)){
                $mapOfWeights[] = array( 
                    'groupNumber' => $element['groupNumber'],
                    'weightList' => array()
                );

                array_push($array1, $element['groupNumber']);
            }

            $key = array_search($element['groupNumber'], $array1);
            array_push($mapOfWeights[$key]['weightList'], $element);
            

            $totalGross += floatval($element['grossWeight']);
            $totalCrate += floatval($element['tareWeight']);
            $totalReduce += floatval($element['reduceWeight']);
            $totalNet += floatval($element['netWeight']);
            $totalCrates += intval($element['numberOfCages']);
            $totalBirds += intval($element['numberOfBirds']);
            
            if($element['birdsPerCages'] != null){
                if(!in_array($element['birdsPerCages'], $array3)){
                    $mapOfBirdsToCages[] = array( 
                        'numberOfBirds' => $element['birdsPerCages'],
                        'count' => 0
                    );
    
                    array_push($array3, $element['birdsPerCages']);
                }
            }
            else{
                $birdsPerCages = (string)((int)$element['numberOfBirds'] / (int)$element['numberOfCages']);
                
                if(!in_array($birdsPerCages, $array3)){
                    $mapOfBirdsToCages[] = array( 
                        'numberOfBirds' => $birdsPerCages,
                        'count' => 0
                    );
    
                    array_push($array3, $birdsPerCages);
                }
            }
            
            if($element['birdsPerCages'] != null){
                 $keyB = array_search($element['birdsPerCages'], $array3);
            }
            else{
                 $birdsPerCages = (string)((int)$element['numberOfBirds'] / (int)$element['numberOfCages']);
                 $keyB = array_search($birdsPerCages, $array3);
            }
            
            $mapOfBirdsToCages[$keyB]['count'] += (int)$element['numberOfCages'];

            if ($element['sex'] == 'Male') {
                $totalMaleBirds += intval($element['numberOfBirds']);
                $totalMaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Female') {
                $totalFemaleBirds += intval($element['numberOfBirds']);
                $totalFemaleCages += intval($element['numberOfCages']);
            } elseif ($element['sex'] == 'Mixed') {
                $totalMixedBirds += intval($element['numberOfBirds']);
                $totalMixedCages += intval($element['numberOfCages']);
            }
        }
    }
    
    // Now you can work with $mapOfWeights and the calculated totals as needed.
}

if(isset($_GET['ids'])){
    $idsParam = $_GET['ids'];
    $idsArray = json_decode($idsParam, true);
    $fileName = '';

    $message = '<html>
    <head>
        <style>
          @media print {
            @page {
              margin-left: .4in;
              margin-right: .4in;
              margin-top: .1in;
              margin-bottom: .1in
            }
          }
        
          table {
            width: 100%;
            border-collapse: collapse
          }
        
          .table td,
          .table th {
            padding: .7rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6
          }
        
          .table-bordered {
            border: 1px dashed #000;
            border-collapse: collapse
          }
        
          .table-bordered td,
          .table-bordered th {
            border: 1px dashed #000;
            font-family: sans-serif;
            font-size: 10px;
            height: 22px
          }
        
          .table-full {
            border: 1px solid #000;
            border-collapse: collapse;
            padding: 0 .7rem
          }
        
          .table-full td,
          .table-full th {
            border: 1px solid #000;
            font-family: sans-serif;
            padding: 0 .7rem
          }
        
          .row {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px
          }
        
          .col-md-3 {
            position: relative;
            width: 25%
          }
        
          .col-md-9 {
            position: relative;
            width: 75%
          }
        
          .col-md-7 {
            position: relative;
            width: 58.333333%
          }
        
          .col-md-5 {
            position: relative;
            width: 41.666667%
          }
        
          .col-md-6 {
            position: relative;
            width: 50%
          }
        
          .col-md-4 {
            position: relative;
            width: 33.333333%
          }
        
          .col-md-8 {
            position: relative;
            width: 66.666667%
          }
          
          #container {
            min-height: 70vh;
            display: table;
            width: 100%;
          }
        
          #footer {
            position: relative;
            padding: 10px 10px 0 10px;
            bottom: 0;
            width: 100%;
            height: 25%
          }
        </style>
    </head>
    <body>';
        

    for($counter=0; $counter<count($idsArray); $counter++){
        $id = $idsArray[$counter];

        if ($select_stmt = $db->prepare("select weighing.*, farms.name FROM weighing, farms WHERE weighing.farm_id = farms.id AND weighing.id=?")) {
            $select_stmt->bind_param('s', $id);
    
            if ($select_stmt->execute()) {
                $result = $select_stmt->get_result();

                if ($row = $result->fetch_assoc()) { 
                    $fileName .= $row['po_no']."_".substr($row['customer'], 0, 15)."_".$row['serial_no'];
                    // Re-initiate
                    $mapOfWeights = array();
                    $totalGross = 0.0;
                    $totalCrate = 0.0;
                    $totalReduce = 0.0;
                    $totalNet = 0.0;
                    $totalCrates = 0;
                    $totalBirds = 0;
                    $totalMaleBirds = 0;
                    $totalMaleCages = 0;
                    $totalFemaleBirds = 0;
                    $totalFemaleCages = 0;
                    $totalMixedBirds = 0;
                    $totalMixedCages = 0;

                    // Start Process
                    $assigned_seconds = strtotime ($row['start_time']);
                    $completed_seconds = strtotime ($row['end_time']);
                    $duration = $completed_seconds - $assigned_seconds;
                    $minutes = floor($duration / 60);
                    $seconds = $duration % 60;
                    
                    // Format minutes and seconds
                    $time = sprintf('%d mins %d secs', $minutes, $seconds);
                    $weightData = json_decode($row['weight_data'], true);
                    $totalWeight = totalWeight($weightData);
                    rearrangeList($weightData);
                    $weightTime = json_decode($row['weight_time'], true);
                    $userName = "Pri Name";
    
                    if($row['weighted_by'] != null){
                        if ($select_stmt2 = $db->prepare("select * FROM users WHERE id=?")) {
                            $uid = json_decode($row['weighted_by'], true)[0];
                            $select_stmt2->bind_param('s', $uid);
        
                            if ($select_stmt2->execute()) {
                                $result2 = $select_stmt2->get_result();
        
                                if ($row2= $result2->fetch_assoc()) { 
                                    $userName = $row2['name'];
                                }
                            }
                        }
                    }

                    $message .= '<div id="container"><table class="table">
                <tbody>
                    <tr>
                        <td style="width: 100%;border-top:0px;text-align:center;"><img src="https://ccb.syncweigh.com/assets/header.png" width="100%" height="auto" /></td>
                    </tr>
                </tbody>
            </table><br>
            
            <table class="table">
                <tbody>
                    <tr>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">';

                        $message .= '<p>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Customer : </span>
                            <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;'.$row['customer'].'</span>
                        </p>';
                            
                        $message .= '</td>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">CCBSB No.: </span>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;color: red;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['po_no'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Farm : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['name'].'</span>
                            </p>
                        </td>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Date : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['start_time'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Total Crates : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.$row['total_cage'].'</span>
                            </p>
                        </td>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Lorry No : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$row['lorry_no'].'</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;border-top:0px;padding: 0 0.7rem;"></td>
                        <td style="width: 40%;border-top:0px;padding: 0 0.7rem;">
                            <p>
                                <span style="font-size: 12px;font-family: sans-serif;font-weight: bold;">Average Crate Wt. : </span>
                                <span style="font-size: 12px;font-family: sans-serif;">'.number_format($row['average_cage'], 2, '.', '').'</span>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table><br>';
            
            $message .= '<table class="table-bordered"><tbody>';
            $count = 1;
            $rowCount = 0;
            $rowTotal = 0;
            $allTotal = 0;
            $indexString = '<tr>';
            
            $count = 1;
            $rows = 1;
            $rowCount = 0;
            $rowTotal = 0;
            $allTotal = 0;
            $indexString = '<tr>';
            
            for ($i = 0; $i < count($weightData); $i++) {
                $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;">'.$weightData[$i]['grossWeight'].'</td>';
                $rowTotal += (float)$weightData[$i]['grossWeight'];
                $allTotal += (float)$weightData[$i]['grossWeight'];

                if($count % 10 == 0){
                    $indexString .= '<td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td></tr>';
                    $rowTotal = 0;
                    $rowCount = 0;
                    $rows++;

                    if($count < count($weightData)){
                        $indexString .= '<tr>';
                    }
                }
                else{
                    $rowCount++;
                }
                
                $count++;
            }

            if ($rowCount > 0) {
                for ($k = 0; $k < (10 - $rowCount); $k++) {
                    if($k == ((10 - $rowCount) - 1)){
                        $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 2, '.', '').'</b></td>';
                    }
                    else{
                        $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td></td>';
                    }
                    
                    $count++;
                }
                $indexString .= '</tr>';
                $rows++;
                $rowCount = 0;
            }
            
            for ($r = 0; $r <= (25 - $rows); $r++) {
                $indexString .= '<tr>';
                
                for ($k = 0; $k < (10 - $rowCount); $k++) {
                    if($k == ((10 - $rowCount) - 1)){
                        $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"></td>';
                    }
                    else{
                        $indexString .= '<td style="width: 4%;text-align: center; center;color: red;">'.$count.'</td><td></td>';
                    }
                    
                    $count++;
                }
                $indexString .= '</tr>';
                $rowCount = 0;
            }
            
            $message .= $indexString;
            $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.number_format($allTotal, 2, '.', '').'</th></tfoot></table>';

            /*for ($j = 0; $j < count($mapOfWeights); $j++) {
                $message .= '<p style="margin: 0px;"><u style="color: blue;">Group No. ' . $mapOfWeights[$j]['groupNumber'] . '</u></p>';
                $message .= '<table class="table-bordered"><tbody>';
                $weightData = $mapOfWeights[$j]['weightList'];

                $count = 1;
                $rowCount = 0;
                $rowTotal = 0;
                $allTotal = 0;
                $indexString = '<tr>';
                
                for ($i = 0; $i < count($weightData); $i++) {
                    $indexString .= '<td style="width: 4%;text-align: center;color: red;">'.$count.'</td><td style="width: 5%;text-align: center;">'.$weightData[$i]['grossWeight'].'</td>';
                    $rowTotal += (float)$weightData[$i]['grossWeight'];
                    $allTotal += (float)$weightData[$i]['grossWeight'];

                    if($count % 10 == 0){
                        $indexString .= '<td style="width: 10%;text-align: center;"><b>'.$rowTotal.'</b></td></tr>';
                        $rowTotal = 0;
                        $rowCount = 0;

                        if($count < count($weightData)){
                            $indexString .= '<tr>';
                        }
                    }
                    else{
                        $rowCount++;
                    }
                    
                    $count++;
                }

                if ($rowCount > 0) {
                    for ($k = 0; $k < (10 - $rowCount); $k++) {
                        if($k == ((10 - $rowCount) - 1)){
                            $indexString .= '<td style="width: 4%;text-align: center;"></td><td style="width: 5%;text-align: center;"></td><td style="width: 10%;text-align: center;"><b>'.number_format($rowTotal, 1, '.', '').'</b></td>';
                        }
                        else{
                            $indexString .= '<td></td><td></td>';
                        }
                    }
                    $indexString .= '</tr>';
                }
                
                $message .= $indexString;
                $message .= '</tbody><tfoot><th colspan="20" style="text-align: right;">Total</th><th>'.$allTotal.'</th></tfoot></table><br>';
            }*/
            
                $message .= '</div><div id="footer">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td style="width: 40%;">
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;"><b>Total Gross Wt.</b></td>
                                                <td style="text-align: center;">'.number_format($totalWeight, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Total Crate Wt.</b></td>
                                                <td style="text-align: center;">'.number_format($totalCrate, 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Total Net Wt. </b></td>
                                                <td style="text-align: center;">'.number_format(($totalWeight - $totalCrate), 2, '.', '').'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Unit Price</b></td>
                                                <td style="text-align: center;"></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Amount</b></td>
                                                <td style="text-align: center;"></td>
                                            </tr>
                                        </tbody>
                                    </table><br>
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;"><b>Birds/Cage</b></td>
                                                <td style="text-align: center;"><b>Cages</b></td>
                                                <td style="text-align: center;"><b>Birds</b></td>
                                            </tr>';
                                        
                                            $totalBirdsInCages = 0;
                                            $totalCages = 0;
                                            for ($bc = 0; $bc < count($mapOfBirdsToCages); $bc++) {
                                                $message .= '<tr>';
                                                $message .= '<td style="text-align: center;">' . $mapOfBirdsToCages[$bc]['numberOfBirds'] . '</td>';
                                                $message .= '<td style="text-align: center;">' . $mapOfBirdsToCages[$bc]['count'] . '</td>';
                                                $message .= '<td style="text-align: center;">' . ((int)$mapOfBirdsToCages[$bc]['count'] * (int)$mapOfBirdsToCages[$bc]['numberOfBirds']) . '</td>';
                                                $message .= '</tr>';
                                                $totalBirdsInCages += ((int)$mapOfBirdsToCages[$bc]['count'] * (int)$mapOfBirdsToCages[$bc]['numberOfBirds']);
                                                $totalCages += (int)$mapOfBirdsToCages[$bc]['count'];
                                            }
                                            
                                            $message .= '<tr>';
                                            $message .= '<td style="text-align: center;"><b>Total</b></td>';
                                            $message .= '<td style="text-align: center;"><b>'.$totalCages.'</b></td>';
                                            $message .= '<td style="text-align: center;"><b>' . $totalBirdsInCages . '</b></td>';
                                            $message .= '</tr>';
                                            
                                        $message .= '</tbody>
                                    </table>
                                </td>
                                <td style="width: 30%;">
                                    <table class="table-full" style="width: 100%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;"><b>Mix.</b></td>
                                                <td style="text-align: center;">'.$totalMixedBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Male</b></td>
                                                <td style="text-align: center;">'.$totalMaleBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Female</b></td>
                                                <td style="text-align: center;">'.$totalFemaleBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Total Birds</b></td>
                                                <td style="text-align: center;">'.$totalBirds.'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Avg. Bird Wt.</b></td>
                                                <td style="text-align: center;">'.number_format((float)$row['average_bird'], 2, '.', '').'</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 30%;">
                                    <table class="table-full" style="width: 90%;">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: center;"><b>Loading Start</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;">'.$row['start_time'].'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;"><b>Loading End</b></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;">'.$row['end_time'].'</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: center;">'.$time.'</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td> 
                            </tr>
                        </tbody>
                    </table>
                </div>';
                    //<p style="page-break-after: always;">&nbsp;</p>';
                }
            }
        }
    }

    $message .= '</body></html>';
    echo $message;
    echo '<script>
        setTimeout(function(){
            document.title = "'.$fileName.'";
            window.print();
            window.close();
        }, 1000);
    </script>';
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    ); 
}

?>