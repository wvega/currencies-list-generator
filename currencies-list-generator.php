<?php

require __DIR__ . '/vendor/autoload.php';

date_default_timezone_set( 'America/Bogota' );

function get_separators( $display_format ) {
    if ( ! $display_format ) {
        return array(
            'thousands_separator' => ',',
            'decimal_point'       => '.',
        );
    }

    if ( preg_match( '/#(?<thousands_separator>[^#]+)###(?<decimal_point>[^#])###?/', $display_format, $matches ) ) {
        return array(
            'thousands_separator' => $matches['thousands_separator'],
            'decimal_point' => $matches['decimal_point'],
        );
    }

    if ( preg_match( '/#(?<thousands_separator>[^#]+)###/', $display_format, $matches ) ) {
        return array(
            'thousands_separator' => $matches['thousands_separator'],
            'decimal_point' => null,
        );
    }

    throw new UnexpectedValueException( sprintf( 'Unknown Display Format: %s.', $display_format ) );
}

$workbook = PHPExcel_IOFactory::load( 'currencies-list.xlsx' );
$sheet = $workbook->getSheetByName( 'Currencies List (Values)' );

$currrencies = array();

foreach ( $sheet->getRowIterator( 3 ) as $row ) {
    $display_format = $sheet->getCell( 'F' . $row->getRowIndex() )->getValue();
    $separators = get_separators( $display_format );

    $currencies[] = array(
        'name'           => $sheet->getCell( 'B' . $row->getRowIndex() )->getValue(),
        'code'           => $sheet->getCell( 'C' . $row->getRowIndex() )->getValue(),
        'symbol'         => $sheet->getCell( 'E' . $row->getRowIndex() )->getValue(),
        'decimal_places' => intval( $sheet->getCell( 'G' . $row->getRowIndex() )->getValue() ),
        'display_format' => $display_format,
        'thousands_separator' => $separators['thousands_separator'],
        'decimal_point' => $separators['decimal_point'],
    );
}


echo "\tprivate \$currencies_codes_by_symbol = array(\n";

foreach ( $currencies as $currency ) {
    if ( ! $currency['symbol'] ) {
        continue;
    }

    echo sprintf( "\t\t'%s' => '%s',\n", $currency['symbol'], $currency['code'] );
}

echo "\t);\n\n";


echo "\tprivate \$currencies_codes_by_country_code = array(\n";

foreach ( $currencies as $currency ) {
    echo sprintf( "\t\t'%s' => '%s',\n", substr( $currency['code'], 0, 2 ), $currency['code'] );
}

echo "\t);\n\n";


echo "\tprivate \$currencies_by_code = array(\n";

foreach ( $currencies as $currency ) {
    echo sprintf( "\t\t'%s' => array(\n", $currency['code'] );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'name', $currency['name'] );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'code', $currency['code'] );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'symbol', $currency['symbol'] );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'decimal_places', $currency['decimal_places'] );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'display_format', str_replace( "'", "\\'", $currency['display_format'] ) );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'thousands_separator', str_replace( "'", "\\'", $currency['thousands_separator'] ) );
    echo sprintf( "\t\t\t'%s' => '%s',\n", 'decimal_point', str_replace( "'", "\\'", $currency['decimal_point'] ) );
    echo "\t\t),\n";
}

echo "\t);\n\n";
