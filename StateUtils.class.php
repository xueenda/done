<?php

date_default_timezone_set('America/New_York');

class StateUtils {

  /*
   * The function we'll call
   *
   * @return int
   */
  public static function calculateTimeInState($testObject, $state = 'RUNNING') {
    $statusLog = $testObject->getStatusLog();
    $startDate = $testObject->getStartDate();
    $stopDate = $testObject->getStopDate();

    // Index $s: the start position in statsLog
    // Index $e: the end position in statsLog
    $s = 0; $e = count($statusLog) - 1;

    for ($i = 0; $i <= $e && ($startDate != null || $stopDate != null); $i++){
      if($startDate && $startDate > $statusLog[$i]['date'])
        $s++;

      if($stopDate && $stopDate < $statusLog[$e]['date'])
       $e--;
    }

    // Index $open: state open position
    // Index $close: state close position
    $duration = 0; $open = -1; $close = -1;
    for($i = $s; $i <= $e; $i++){
      if($statusLog[$i]['newState'] == $state && $statusLog[$i]['oldState'] != $state)
        $open = $i;

      if($statusLog[$i]['oldState'] == $state && $statusLog[$i]['newState'] != $state)
        $close = $i;

      if($open != -1 && $close > $open)
        $duration += $statusLog[$close]['date'] - $statusLog[$open]['date'];
    }

    // Edge case: In open state after the loop
    if($open > $close || ($open >=0 && $close ==-1))
      $duration += ($stopDate ? $stopDate : time()) - $statusLog[$open]['date'];

    // Edge case: Start date is greater than any log and the last state is open
    if($startDate && $open == -1 && $close == -1 && 
        $statusLog[count($statusLog) - 1]['newState'] == $state)
      $duration = time() - $startDate;

    return $duration;
  }
}

?>