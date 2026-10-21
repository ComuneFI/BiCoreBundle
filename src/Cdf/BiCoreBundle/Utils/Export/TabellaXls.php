<?php

namespace Cdf\BiCoreBundle\Utils\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Cdf\BiCoreBundle\Utils\Entity\DoctrineFieldReader;

class TabellaXls
{

    public function __construct(private readonly string $tableprefix)
    {
    }

    /**
     *
     * @param array<mixed> $parametri
     */
    public function esportaexcel(array $parametri = []): string
    {
        set_time_limit(960);
        ini_set('memory_limit', '2048M');

        //Creare un nuovo file
        $spreadsheet = new Spreadsheet();
        $objPHPExcel = new Xls($spreadsheet);
        $spreadsheet->setActiveSheetIndex(0);

        // Set properties
        $spreadsheet->getProperties()->setCreator('Comune di Firenze');
        $spreadsheet->getProperties()->setLastModifiedBy('Comune di Firenze');

        $header = $parametri['parametritabella'];
        $rows = $parametri['recordstabella'];
        //Scrittura su file
        $sheet = $spreadsheet->getActiveSheet();
        $titolosheet = 'Esportazione ' . $parametri['nomecontroller'];
        $sheet->setTitle(substr($titolosheet, 0, 30));
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Verdana');

        $this->printHeaderXls($header, $sheet);

        $this->printBodyXls($header, $rows, $sheet);

        //Si crea un oggetto
        $todaydate = date('d-m-y');

        $filename = 'Exportazione';
        $filename = $filename . '-' . $todaydate . '-' . strtoupper(md5(uniqid((string) random_int(0, mt_getrandmax()), true)));
        $filename .= '.xls';
        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($filename)) {
            unlink($filename);
        }

        $objPHPExcel->save($filename);

        return $filename;
    }

    /**
     *
     * @param array<mixed> $testata
     */
    private function printHeaderXls(array $testata, Worksheet $worksheet): void
    {
        $indicecolonnaheader = 1;
        $letteracolonna = 0;
        foreach ($testata as $modellocolonna) {
            if (false === $modellocolonna['escluso']) {
                //Si imposta la larghezza delle colonne
                $letteracolonna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indicecolonnaheader);
                $width = (int) $modellocolonna['larghezza'] / 7;
                $indicecolonnaheadertitle = $modellocolonna['etichetta'];
                $coltitle = strtoupper($indicecolonnaheadertitle);
                $worksheet->setCellValueByColumnAndRow($indicecolonnaheader, 1, $coltitle);
                $worksheet->getColumnDimension($letteracolonna)->setWidth($width);

                ++$indicecolonnaheader;
            }
        }

        //Imposta lo stile per l'intestazione un po più decente
        $this->setHeaderStyle($worksheet, $indicecolonnaheader - 1);

        $worksheet->getRowDimension(1)->setRowHeight(20);
    }

    /**
     *
     * @param array<mixed> $header
     * @param array<mixed> $rows
     */
    private function printBodyXls($header, $rows, Worksheet $worksheet): void
    {
        $row = 2;
        foreach ($rows as $riga) {
            $col = 1;
            foreach ($header as $colonnatestata => $valorecolonnatestata) {
                if (false === $valorecolonnatestata['escluso']) {
                    $dfr = new DoctrineFieldReader($this->tableprefix);

                    $decodiche = $valorecolonnatestata['decodifiche'] ?? null;
                    $oggetto = $dfr->getField2Object($colonnatestata, $riga, $decodiche);
                    $valorecampo = $dfr->object2view($oggetto, $valorecolonnatestata['tipocampo']);
                    if ('' === $valorecampo || null === $valorecampo) {
                        $col += 1;
                        continue;
                    }
                    //Fix https://github.com/ComuneFI/BiCoreBundle/issues/9
                    $xlsvalue = $this->getValueCell($valorecolonnatestata['tipocampo'], $valorecampo);
                    if (str_starts_with($xlsvalue, '=')) {
                        $worksheet->setCellValueExplicitByColumnAndRow($col, $row, $xlsvalue, DataType::TYPE_STRING2);
                    } else {
                        $worksheet->setCellValueByColumnAndRow($col, $row, $xlsvalue);
                    }
                    $col += 1;
                }
            }
            $worksheet->getRowDimension($row)->setRowHeight(18);
            ++$row;
        }

        $col = 1;
        //Si impostano i formati cella in base al tipo di dato contenuto
        foreach ($header as $valorecolonnatestata) {
            if (false === $valorecolonnatestata['escluso']) {
                $this->setCellColumnFormat($worksheet, $col, $row - 1, $valorecolonnatestata['tipocampo']);
                $this->setColumnAutowidth($worksheet, $col);
                $col += 1;
            }
        }
    }

    
    private function setHeaderStyle(Worksheet $worksheet, int $indiceultimacolonna): void
    {
        $letteraultimacolonna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indiceultimacolonna);
        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'e5e5e5e5',
                ],
            ],
        ];

        $worksheet->getStyle('A1:' . $letteraultimacolonna . '1')->applyFromArray($styleArray);
    }

    /**
     *
     * @param mixed $valorecella
     * @return mixed|null
     */
    private function getValueCell(string $tipocampo, $valorecella)
    {
        $valore = null;
        switch ($tipocampo) {
            case 'date':
                $d = (int) substr($valorecella, 0, 2);
                $m = (int) substr($valorecella, 3, 2);
                $y = (int) substr($valorecella, 6, 4);
                $t_date = \PhpOffice\PhpSpreadsheet\Shared\Date::formattedPHPToExcel($y, $m, $d);
                $valore = $t_date;
                break;
            case 'datetime':
                $d = (int) substr($valorecella, 0, 2);
                $m = (int) substr($valorecella, 3, 2);
                $y = (int) substr($valorecella, 6, 4);
                $h = (int) substr($valorecella, 11, 2);
                $i = (int) substr($valorecella, 14, 2);
                $t_date = \PhpOffice\PhpSpreadsheet\Shared\Date::formattedPHPToExcel($y, $m, $d, $h, $i, 0);
                $valore = $t_date;
                break;
            default:
                $valore = $valorecella;
                break;
        }

        return $valore;
    }

    private function setCellColumnFormat(Worksheet $worksheet, int $indicecolonna, int $lastrow, string $tipocampo): void
    {
        $letteracolonna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indicecolonna);
        match ($tipocampo) {
            'integer' => $worksheet->getStyle($letteracolonna . '2:' . $letteracolonna . $lastrow)
                    ->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER),
            'float', 'decimal', 'number' => $worksheet->getStyle($letteracolonna . '2:' . $letteracolonna . $lastrow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00'),
            //\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYYSLASH
            'datetime' => $worksheet->getStyle($letteracolonna . '2:' . $letteracolonna . $lastrow)
                    ->getNumberFormat()
                    ->setFormatCode('dd/mm/yyyy hh:mm:ss'),
            'date' => $worksheet->getStyle($letteracolonna . '2:' . $letteracolonna . $lastrow)
                    ->getNumberFormat()
                    ->setFormatCode('dd/mm/yyyy'),
            default => $worksheet->getStyle($letteracolonna . '2:' . $letteracolonna . $lastrow)
                    ->getNumberFormat()
                    ->setFormatCode('@'),
        };
    }

    private function setColumnAutowidth(Worksheet $worksheet, int $indicecolonna) : void
    {
        $letteracolonna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($indicecolonna);
        $worksheet->getColumnDimension($letteracolonna)->setAutoSize(true);
    }
}
