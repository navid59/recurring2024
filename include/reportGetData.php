<?php
$obj = new recurringAdmin();
$arrayData = $obj->getReportList();

if(isset($arrayData['code']) && ($arrayData['code'] == 11 || $arrayData['code'] == 12)) {
    echo '
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h2 class="alert-heading">'.__('Note! ','ntpRp').'</h2>
        <strong>'.__('Reports will register after any try for payment.','ntpRp').'</strong><br>'
        .__('If you suppose to have reports and is not registered any for you, check your configuration. please.','ntpRp')
        .'<hr>
        <p class="mb-0"><strong>'.__('Details: ','ntpRp').'</strong>'.$arrayData['message'].'</p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>';
} else {
    foreach($arrayData['report']  as $payArchive) {
        echo "<tr>";
            printf("<td>%1s</td>",$payArchive['id']);
            printf("<td>%1s</td>",$payArchive['UserId']);
            printf("<td>%1s</td>",$payArchive['Title']);
            printf("<td>%1s</td>",$payArchive['Amount']);
            printf("<td>%1s</td>",$obj->getStatusStr('report',$payArchive['Status']));
            printf("<td>%1s</td>",$payArchive['Comment']);            
            printf("<td>%1s</td>",$payArchive['TransactionID']);
            $date = new DateTime($payArchive['CreatedAt']);
            printf("<td>%1s</td>",$date->format('Y-m-d'));
        echo "</tr>";
    }
}
?>