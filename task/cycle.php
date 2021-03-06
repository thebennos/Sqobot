<?php namespace Sqobot;

class TaskCycle extends Task {
  const YEAR = 31104000;

  function do_(array $args = null) {
    $queuer = Task::make('queue');

    if ($args === null) {
      echo 'cycle [...] - accepts all parameters of `queue [do]`, plus:', PHP_EOL,
           '  --max=TIMES --for=MINUTES --delay=1[000] --no-ignore', PHP_EOL, PHP_EOL;
      $queuer->call('', null);
      return;
    }

    $args += array('max' => -1, 'for' => '', 'delay' => cfg('remoteDelay'));

    $times = 1 + $args['max'];
    $until = $args['for'] ? (int) (time() + 60 * $args['for']) : PHP_INT_MAX;
    $delay = (int) $args['delay'];
    if ($delay > 0 and $delay < 50) { $delay *= 1000; }

    echo 'Delay between iterations is ', $delay, ' msec.', PHP_EOL;

    if ($times < 1 and $until > time() + static::YEAR) {
      echo 'Warning: no conditions given, running forever.', PHP_EOL, PHP_EOL;
    }

    for ($i = 1; $i != $times and time() < $until; ++$i) {
      $i > 1 and remoteDelay(null, $delay);

      echo PHP_EOL,
           $separ = '+'.str_repeat('-', 68).'+', PHP_EOL,
           sprintf('> ITERATION %-15s %40s <', $i, date('d.m.Y H:i:s')), PHP_EOL,
           $separ, PHP_EOL, PHP_EOL;

      try {
        $queuer->call('', $args);
      } catch (\Exception $e) {
        echo 'Exception: ', exLine($e), PHP_EOL;
        if (!empty($args['no-ignore'])) { break; }
      }
    }

    $s = --$i == 1 ? '' : 's';
    echo PHP_EOL, "* Stopped after $i iteration$s.", PHP_EOL;
  }
}