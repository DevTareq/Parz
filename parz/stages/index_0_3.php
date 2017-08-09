<?php
/**
 * Created by PhpStorm.
 * User: tareq
 * Date: 8/7/17
 * Time: 10:40 AM
 */
// json input sample

$headers    = getHeaders();
$values     = getHeadersValues($headers['recordType']);
$mergedData = mergeHeadersValues($headers, $values);


echo "**********************************************************\n";
echo "*************      SINGLE HEADER VALUE      **************\n";
echo "**********************************************************\n";


echo "*******************************************\n";
echo "*************      HEADERS      ***********\n";
echo "*******************************************\n";

print_r($headers);

echo "*******************************************\n";
echo "******      NESTED HEADERS       **********\n";
echo "*******************************************\n";

print_r($values);

echo "*******************************************\n";
echo "******       MERGED VALUES       **********\n";
echo "*******************************************\n";

print_r($mergedData);

// parse into excel


function getHeaders()
{

    $fp = fopen('_data/orders_0_3.iif', 'r');

    $result        = [];
    $nestedHeaders = [];
    $endType       = '';
    $startType     = '';

    if (($headers = fgetcsv($fp, 0, "\t")) !== false) {
        if ($headers) {

            if (0 === strpos($headers[0], '!')) {
                $startType = $headers[0];
            }

            while (($line = fgetcsv($fp, 0, "\t")) !== false) {
                if ($line) {

                    if (0 === strpos($line[0], '!')) {

                        if (sizeof($line) == sizeof($headers)) {
                            $nestedHeaders[] = $line;
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


function getHeadersValues($recordType)
{
    $fp = fopen('_data/orders_0_3.iif', 'r');

    $result = [];

    while (!feof($fp)) {
        $line = fgets($fp, 2048);

        if (0 !== strpos($line[0], '!')) {


            $data = str_getcsv($line, "\t");

            if (count($data) > 1) {
                // @TODO fix if statement

                $lineArray = explode("\t", $line);

                if ('!' . $lineArray[0] == $recordType) { // @TODO FIX STATIC VALUE
                    $result['headersValues'] = $data;
                } else {
                    $result['nestedHeadersValues'][] = $data;
                }
            }
        }

    }

    fclose($fp);

    return $result;
}


function mergeHeadersValues($headers, $values)
{

    $mergedValues = [];

    $count = 0;

    foreach ($headers['recordHeaders'] as $header) {

        if (isset($headers['recordHeaders'][$count])) {
            $mergedValues['headersValues'][$headers['recordHeaders'][$count]] = $values['headersValues'][$count];
        }

        $count++;
    }

    // remove one level up from the array
    $nestedHeaders = call_user_func_array('array_merge', $headers['recordNestedHeaders']);

    $record = [];
    foreach ($values['nestedHeadersValues'] as $innerValue) {

        $secondCount = 0;

        foreach ($innerValue as $inner) {

            $record[$nestedHeaders[$secondCount]] = $inner;
            $secondCount++;

        }

        $mergedValues['nestedHeadersValues'][] = $record;

    }

    return $mergedValues;
}

// export