<?php
/**
 * Created by PhpStorm.
 * User: tareq
 * Date: 8/7/17
 * Time: 10:40 AM
 */

CONST VERSION   = '7';
CONST FILE_PATH = '_data/orders_0_' . VERSION . '.iif';


$values        = getHeadersValues();
$formedResults = formResults($values);


//echo "**********************************************************\n";
//echo "******************      MIXED TYPES       ****************\n";
//echo "**********************************************************\n";
//
//
echo "*******************************************\n";
echo "******      TEST ALL EXAMPLES      ********\n";
echo "*******************************************\n";

print_r($formedResults);

//echo "*******************************************\n";
//echo "******       MERGED VALUES       **********\n";
//echo "*******************************************\n";
//
//print_r($mergedData);

// parse into excel


function getHeaders()
{

    $fp         = fopen(FILE_PATH, 'r');
    $result     = [];
    $tempHeader = [];


    while (($line = fgetcsv($fp, 0, "\t")) !== false) {

        if ($line) {
            if (0 === strpos($line[0], '!')) {
                if (0 !== strpos($line[0], '!END')) {
                    $nestedHeaders[$line[0]] = $line;
                }
            }

            if (0 === strpos($line[0], 'END')) {
                $tempHeader[] = str_replace('END', '!', $line[0]);
            }
        }
    }

    $result = [
        'allHeaders'  => $nestedHeaders,
        'rootHeaders' => $tempHeader,
    ];


    fclose($fp);

    return $result;

}


function getHeadersValues()
{
    $headers = getHeaders();

    $tempHeaderValues = [];
    $fp               = fopen(FILE_PATH, 'r');

    $count = 0;
    while (!feof($fp)) {
        $line = fgets($fp, 2048);

        if (0 !== strpos($line[0], '!')) { // check first character

            $lineArray  = explode("\t", trim($line));
            $tempArray  = [];
            $innerCount = 0;

            // check if we have the arrays headers by looking for a key with the same first type
            foreach ($lineArray as $innerValue) {
                if (isset($headers['allHeaders']['!' . $lineArray[0]])) {
                    $tempArray[$headers['allHeaders']['!' . $lineArray[0]][$innerCount]] = $innerValue;
                }

                $innerCount++;
            }

            if (!empty($lineArray[0])) {

                // if the first element starts with END
                if (strpos($lineArray[0], 'END') !== 0) {

                    // check if nested header is the same as parent
                    if (in_array('!' . $lineArray[0], $headers['rootHeaders'])) {
                        $tempHeaderValues[$count]['header'] = $tempArray;
                    } else {
                        $tempHeaderValues[$count]['nested'] = $tempArray;
                    }

                    $count++;
                }
            }
        }
    }

    $tempHeaderValues = array_values($tempHeaderValues);

    fclose($fp);

    return $tempHeaderValues;
}

/**
 * 1. We loop though the $values array
 * 2. we check if the current element is header, then we store it in temp variable
 * 3. we check if the current element is nested, then we store it in temp variable
 * 4. we check if we faced header again, we reset the values and we set the old values in global array
 * 5. we check if the current element is the last element in the array, then we reset the values and add the old ones
 * global array
 * 6. we merge the headers array with the values array
 *
 * @param $values
 *
 * @return array
 */
function formResults($values)
{
    // reset array index
    $values     = array_values($values);
    $record     = [];
    $tempNested = [];
    $tempHeader = '';

    for ($i = 0; $i < sizeof($values); $i++) {

        if (isset($values[$i]['header'])) {

            if ((!empty($tempHeader) && !empty($tempNested)) ||
                (!empty($tempHeader) && empty($tempNested))
            ) {
                $record[]   = ['headers' => $tempHeader, 'nested' => $tempNested];
                $tempHeader = [];
                $tempNested = [];
            }

            $tempHeader = $values[$i]['header'];
        }

        if (isset($values[$i]['nested'])) {
            $tempNested[] = $values[$i]['nested'];
        }

        // check for the last element in array
        if ($i == count($values) - 1) {

            $record[]   = ['headers' => $tempHeader, 'nested' => $tempNested];
            $tempHeader = [];
            $tempNested = [];
        }
    }

    return $record;

}

// export