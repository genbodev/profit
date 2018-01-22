<?php

$start = microtime(true);
ini_set('max_execution_time', 900);
$errors = array();

if (isset($_POST['min']) &&
    isset($_POST['max']) &&
    isset($_POST['quantity'])
) {

    bcscale(3);

    // Накопитель возвращаемых данных
    $returnData = array();

    $rand = null;
    // Массив для определение моды
    $group = array();
    // Накапливаемая сумма для расчета среднего значения
    $sum_average = 0;
    // Накапливаемая сумма для расчета стандартного отклонения
    $sum_deviation = 0;

    $count = $_POST['quantity'];
    $returnData['count'] = $count;

    $mem_start = memory_get_usage();

    // Центральное значение для расчета медианы для нечетного ряда
    $middle_center = null;
    // Крайнее левое значение для расчета медианы для четного ряда
    $middle_low = null;
    // Крайнее правое значение для расчета медианы для четного ряда
    $middle_high = null;
    // Сразу определяем крайнее лнвое или центральное значение
    $middle = floor(bcsub($count, 1) / 2);
    if (bcmod($count, 2) > 0) {
        // Нечетный ряд - останавливаемся только на центральном значении
        $middle_center = $middle;
    } else {
        // Четный ряд - определяем крайние левое и правое значения
        $middle_low = $middle;
        $middle_high = $middle + 1;
    }

    $i = 0;
    // Значение медианы для нечетного ряда
    $median_center = null;
    // Знвчения для расчета медианы для четного ряда
    $median_low = null; // Крайнее левое
    $median_high = null; // Крайнее правое
    while ($i < $count) {
        $rand = mt_rand($_POST['min'], $_POST['max']);
        $group[$rand] = $group[$rand] + 1;
        if ($middle_center !== null && intval($middle_center) === $i) {
            $median_center = $rand;
        } else {
            if ($middle_low !== null && intval($middle_low) === $i) {
                $median_low = $rand;
            }
            if ($middle_high !== null && intval($middle_high) === $i) {
                $median_high = $rand;
            }
        }
        $sum_average = bcadd($sum_average, $rand); // s1 += d;
        $sum_deviation = bcadd($sum_deviation, bcmul($rand, $rand)); // s2 += d * d;
        $i = $i + 1;
    }

    $average = getAverage($sum_average, $count);
    $returnData['average'] = $average;

    $deviation = getDeviation($sum_deviation, $average, $count);
    $returnData['deviation'] = $deviation;

    $a = ((memory_get_usage() - $mem_start) / 1024) / 1024;

    $mode = getMode($group);
    $returnData['mode'] = $mode;
    unset($group);

    $median = getMedian($median_center, $median_low, $median_high);
    $returnData['median'] = $median;
    unset($numbers);

    $returnData['peak_requested'] = (int) (memory_get_peak_usage() / 1024) . ' KB';
    $returnData['peak_allocated'] = (int) (memory_get_peak_usage(true) / 1024) . ' KB';

    $time = microtime(true) - $start;
    $returnData['time'] = $time;

    $returnData['memory_limit'] = ini_get('memory_limit');

    echo json_encode($returnData);

}

function getAverage($sum_average, $count) {
    return bcdiv($sum_average, $count);
}

function getDeviation($sum_deviation, $average, $count) {
    // Это расширение не поставляется вместе с PHP.
    // return stats_standard_deviation($arr);

    // Обход отрицательной дисперсии
    // https://en.wikipedia.org/wiki/Standard_deviation#Rapid_calculation_methods

    // sqrt(abs(($count * $average * $average) - $sum_deviation) / ($count - 1));
    return bcsqrt(abs(bcsub(bcmul($count, bcmul($average, $average)), $sum_deviation)) / bcsub($count, 1));
}

function getMode(&$group) {
    $mode = array();
    $c = 1;
    foreach ($group as $key => $value) {
        if ($value > 1) {
            if ($value > $c) {
                $mode = array();
                $mode[] = $key;
                $c = $value;
            } else if ($value === $c) {
                $mode[] = $key;
            }
        }
    }
    return $mode;
}

function getMedian($median_center, $median_low, $median_high) {
    if ($median_center !== null) {
        $median = $median_center;
    } else {
        $median = bcdiv(bcadd($median_low, $median_high), 2);
    }
    return $median;
}

