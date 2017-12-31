<?php

date_default_timezone_set('America/New_York');

if (class_exists('SomeObject'))
  require_once __DIR__ . '/SomeObject.class.php';

class StateUtils {

  /*
   * The function we'll call
   *
   * @return int
   */
  public static function calculateTimeInState($testObject, $state = 'RUNNING') {
    if(!is_a($testObject, 'SomeObject'))
      throw new InvalidArgumentException('$testObject was not of type SomeObject');

    $statusLog = $testObject->getStatusLog();
    $startDate = $testObject->getStartDate();
    $stopDate = $testObject->getStopDate();

    if(!$statusLog || !$statusLog[0])
      return 0;

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

    if($startDate && $statusLog[$s]['newState'] == $state && $statusLog[$s]['oldState'] == $state)
        $open = $s;

    for($i = $s; $i <= $e; $i++){
      if($statusLog[$i]['newState'] == $state && $statusLog[$i]['oldState'] != $state)
        $open = $i;

      if($statusLog[$i]['oldState'] == $state && $statusLog[$i]['newState'] != $state)
        $close = $i;

      if($open != -1 && $close > $open)
        $duration += $statusLog[$close]['date'] - (
            $open == $s && $startDate ? $startDate : $statusLog[$open]['date']
          );
    }

    // Edge case: In open state after the loop
    if($open > $close || ($open >=0 && $close ==-1))
      $duration += ($stopDate ? $stopDate : time()) - $statusLog[$open]['date'];

    // Edge case: Start date is greater than any logs and the last state is open
    if($startDate && $open == -1 && $close == -1 && 
        $statusLog[count($statusLog) - 1]['newState'] == $state)
      $duration = time() - $startDate;

    return $duration;
  }
}

?>