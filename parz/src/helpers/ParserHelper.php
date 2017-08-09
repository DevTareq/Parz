<?php

namespace Helpers;

use Core\Constants;

/**
 * Class ParserHelper
 *
 * @author  Tareq Albajjaly <dev.tareq@gmail.com>
 * @version 0.1
 */
class ParserHelper
{
    /**
     * Parse file data.
     *
     * @return array
     */
    public static function parse(): array
    {
        $headers = self::getHeadersStructure();

        $tempHeaderValues = [];
        $file             = fopen(Constants::FILE_PATH, 'r');

        $count = 0;
        while (!feof($file)) {
            $line = fgets($file, 2048);

            if ($line) {
                // Check first character, determine headers prefix
                if (0 !== strpos($line[0], Constants::HEADER_PREFIX)) {

                    $lineArray  = explode(Constants::SEPARATOR_TYPE, trim($line));
                    $tempArray  = [];
                    $innerCount = 0;

                    // Check for the array structure in headers arrays, by looking up the key in $headers
                    foreach ($lineArray as $innerValue) {
                        if (isset($headers['allHeaders'][Constants::HEADER_PREFIX . $lineArray[0]])) {
                            $tempArray[$headers['allHeaders'][Constants::HEADER_PREFIX . $lineArray[0]][$innerCount]] = $innerValue;
                        }

                        $innerCount++;
                    }

                    if (!empty($lineArray[0])) {

                        // If the first element starts with END
                        if (strpos($lineArray[0], Constants::HEADER_END_PREFIX) !== 0) {

                            // check if nested header is the same as parent
                            if (in_array(Constants::HEADER_PREFIX . $lineArray[0], $headers['rootHeaders'])) {
                                $tempHeaderValues[$count]['header'] = $tempArray;
                            } else {
                                $tempHeaderValues[$count]['nested'] = $tempArray;
                            }

                            $count++;
                        }
                    }
                }
            }
        }

        $tempHeaderValues = array_values($tempHeaderValues);

        fclose($file);

        $mappedValues = self::mapHeadersValues($tempHeaderValues) ?? [];

        if (!empty($mappedValues)) {
            return $mappedValues;
        }

        return 'No data found!';
    }

    /**
     * Get headers structure.
     *
     * @return array
     */
    private static function getHeadersStructure(): array
    {
        $file       = fopen(Constants::FILE_PATH, 'r');
        $tempHeader = [];

        while (($line = fgetcsv($file, 0, Constants::SEPARATOR_TYPE)) !== false) {

            if ($line) {
                // Check if it starts with the header prefix

                if (0 === strpos($line[0], Constants::HEADER_PREFIX)) {
                    // Check if it doesn't start with headers prefix, therefor its a nested header

                    if (0 !== strpos($line[0], Constants::HEADER_PREFIX . Constants::HEADER_END_PREFIX)) {
                        $nestedHeaders[$line[0]] = $line;
                    }
                }

                if (0 === strpos($line[0], Constants::HEADER_END_PREFIX)) {
                    $tempHeader[] = str_replace(Constants::HEADER_END_PREFIX, Constants::HEADER_PREFIX, $line[0]);
                }
            }
        }

        $result = [
            'allHeaders'  => $nestedHeaders ?? [],
            'rootHeaders' => $tempHeader ?? [],
        ];


        fclose($file);

        return $result;
    }

    /**
     * Map headers values.
     * Restructure the results array.
     *
     * @param array $values
     *
     * @return array
     */
    private static function mapHeadersValues(array $values): array
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

            // Check for the last element in array
            if ($i == count($values) - 1) {

                $record[]   = ['headers' => $tempHeader, 'nested' => $tempNested];
                $tempHeader = [];
                $tempNested = [];
            }
        }

        return $record;
    }
}