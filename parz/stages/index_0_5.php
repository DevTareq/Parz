<?php
/**
 * Created by PhpStorm.
 * User: tareq
 * Date: 8/7/17
 * Time: 10:40 AM
 */
// json input sample

$values     = getHeadersValues();
$mergedData = formResults($values);


//echo "**********************************************************\n";
//echo "******************      MIXED TYPES       ****************\n";
//echo "**********************************************************\n";
//
//
echo "*******************************************\n";
echo "******      NESTED HEADERS       **********\n";
echo "*******************************************\n";

print_r($mergedData);

//echo "*******************************************\n";
//echo "******       MERGED VALUES       **********\n";
//echo "*******************************************\n";
//
//print_r($mergedData);

// parse into excel


function getHeaders()
{

    $fp = fopen('_data/orders_0_5.iif', 'r');

    $result        = [];
    $nestedHeaders = [];
    $endType       = '';
    $startType     = '';
    $tempType      = '';

    if (($headers = fgetcsv($fp, 0, "\t")) !== false) {
        if ($headers) {

            if (0 === strpos($headers[0], '!')) {
                $startType = $headers[0];
                $tempType  = $headers[0];
            }

            while (($line = fgetcsv($fp, 0, "\t")) !== false) {

                if ($line) {
                    if (0 === strpos($line[0], '!')) {

                        if (sizeof($line) == sizeof($headers)) {

                            // check if the nested header is the same as parent
                            if ($tempType !== $line[0]) {
                                $nestedHeaders[] = $line;
                            }

                        } else {
                            if ($line[0] == '!END' . str_replace('!', '', $startType)) {
                                $endType = $line[0];
                            }
                        }
                    }

                }

                $result = ['recordType'          => $startType,
                           'recordTypeEnd'       => $endType,
                           'recordHeaders'       => $headers,
                           'recordNestedHeaders' => $nestedHeaders];

            }

        }

        fclose($fp);

        return $result;
    }
}


function getHeadersValues()
{
    $headers          = getHeaders();
    $tempHeaderValues = [];
    $fp               = fopen('_data/orders_0_5.iif', 'r');

    $count = 0;
    while (!feof($fp)) {
        $line = fgets($fp, 2048);

        if (0 !== strpos($line[0], '!')) { // check first character

            $lineArray = explode("\t", trim($line));

            if (!empty($lineArray[0])) {

                if ($lineArray[0] !== 'END' . str_replace('!', '', $headers['recordType'])) {

                    // check if nested header is the same as parent
                    if ('!' . $lineArray[0] == $headers['recordType']) {
                        $tempHeaderValues[$count]['header'] = $lineArray;
                    } else {
                        $tempHeaderValues[$count]['nested'] = $lineArray;
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
    $headers = getHeaders();

    // reset array index
    $values     = array_values($values);
    $record     = [];
    $tempNested = [];
    $tempHeader = '';

    for ($i = 0; $i < sizeof($values); $i++) {

        if (isset($values[$i]['header'])) {

            if (!empty($tempHeader) && !empty($tempNested)) {
                $record[]   = ['headers' => $tempHeader, 'nested' => $tempNested];
                $tempHeader = [];
                $tempNested = [];
            }

            $tempHeader = $values[$i]['header'];
        }

        if (isset($values[$i]['nested'])) {
            $tempNested[] = $values[$i]['nested'];
        }

        // check for the last lement in array
        if ($i == count($values) - 1) {
            $record[]   = ['headers' => $tempHeader, 'nested' => $tempNested];
            $tempHeader = [];
            $tempNested = [];
        }
    }

    return array_merge($headers, $record);

}


// export