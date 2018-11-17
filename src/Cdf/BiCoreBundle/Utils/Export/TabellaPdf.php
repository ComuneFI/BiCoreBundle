<?php

namespace Cdf\BiCoreBundle\Utils\Export;

//use Cdf\BiCoreBundle\Utils\GrigliaFiltriUtils;
//use TCPDF;

class TabellaPdf
{
    /*public function stampa($parametri = array())
    {
        $testata = $parametri['testata'];
        $request = $parametri['request'];
        $nometabella = $request->get('nometabella');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        //echo PDF_HEADER_LOGO;
        $pdftitle = isset($testata['titolo']) && ($testata['titolo'] != '') ? $testata['titolo'] : 'Elenco ' . $nometabella;
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'FiFree2', $pdftitle, array(0, 0, 0), array(0, 0, 0));
        $pdf->setFooterData(array(0, 0, 0), array(0, 0, 0));

        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetFillColor(220, 220, 220);

        $pdf->AddPage('L');
        $arraystampaparm = array(
            "larghezzaform" => 900,
            "h" => 6,
            "border" => 1,
            "align" => 'L',
            "fill" => 0,
            "ln" => 0
        );
        $parametristampa = array_merge($parametri, $arraystampaparm);

        $this->stampaTestata($pdf, $parametristampa);

        $this->stampaDettaglio($pdf, $parametristampa);


//          I: send the file inline to the browser (default). The plug-in is used if available.
//          The name given by name is used when one selects the “Save as” option on the link generating the PDF.
//          D: send to the browser and force a file download with the name given by name.
//          F: save to a local server file with the name given by name.
//          S: return the document as a string (name is ignored).
//          FI: equivalent to F + I option
//          FD: equivalent to F + D option
//          E: return the document as base64 mime multi-part email attachment (RFC 2045)
//
//
//         In caso il pdf stampato nel browser resti fisso a caricare la pagina,
//          impostare 'D' per forzare lo scarico del file, oppure
//          mettere exit al posto di return 0; questo opzione però non è accettata da gli strumenti di controllo del codice che non si
//          aspettano exit nel codice

        $pdf->Output($request->get('nometabella') . '.pdf', 'I');

        return 0;
    }
    private function stampaTestata($pdf, $parametri)
    {
        $ln = $parametri['ln'];
        $fill = $parametri['fill'];
        $align = $parametri['align'];
        $border = $parametri['border'];
        $h = $parametri['h'];
        $larghezzaform = $parametri['larghezzaform'];
        $testata = $parametri['testata'];
        $nomicolonne = $testata['nomicolonne'];
        $modellicolonne = $testata['modellocolonne'];

        // Testata
        $pdf->SetFont('helvetica', 'B', 9);
        $arr_heights = array();
        // store current object
        $pdf->startTransaction();
        foreach ($nomicolonne as $posizione => $nomecolonna) {
            $width = $this->getWidthColumn($modellicolonne, $posizione, $larghezzaform);
            // get the number of lines
            $arr_heights[] = $pdf->MultiCell($width, 0, $nomecolonna, $border, $align, $fill, 0, '', '', true, 0, false, true, 0);
        }
        // restore previous object
        $pdf->rollbackTransaction(true);
        //work out the number of lines required
        $rowcount = max($arr_heights);
        //now draw it
        foreach ($nomicolonne as $posizione => $nomecolonna) {
            $width = $this->getWidthColumn($modellicolonne, $posizione, $larghezzaform);
            $pdf->MultiCell($width, $rowcount * $h, $nomecolonna, $border, $align, $fill, $ln);
        }
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Ln();
    }
    private function stampaDettaglio($pdf, $parametri)
    {

        $ln = $parametri['ln'];
        $fill = $parametri['fill'];
        $align = $parametri['align'];
        $border = $parametri['border'];
        $h = $parametri['h'];
        $larghezzaform = $parametri['larghezzaform'];
        $testata = $parametri['testata'];
        $modellicolonne = $testata['modellocolonne'];

        $rispostaj = $parametri['griglia'];
        // Dati
        $risposta = json_decode($rispostaj);
        $dimensions = $pdf->getPageDimensions();
        $righe = $risposta->rows;
        $pdf->SetFont('helvetica', '', 9);
        foreach ($righe as $riga) {
            $fill = !$fill;
            $vettorecelle = $riga->cell;

            $arr_heights = array();
            // store current object
            $pdf->startTransaction();
            foreach ($vettorecelle as $posizione => $valore) {
                if (!is_object($valore)) {
                    $width = $this->getWidthColumn($modellicolonne, $posizione, $larghezzaform);
                    // get the number of lines
                    $arr_heights[] = $pdf->MultiCell($width, 0, $valore, $border, $align, $fill, 0, '', '', true, 0, false, true, 0);
                }
            }
            // restore previous object
            $pdf->rollbackTransaction(true);
            //work out the number of lines required
            $rowcount = max($arr_heights);
            $startY = $pdf->GetY();
            if (($startY + $rowcount * $h) + $dimensions['bm'] > ($dimensions['hk'])) {
                // page break
                $pdf->AddPage('L');
                // stampa testata
                $this->stampaTestata($pdf, $parametri);
            }
            //now draw it
            foreach ($vettorecelle as $posizione => $valore) {
                if (!is_object($valore)) {
                    $width = $this->getWidthColumn($modellicolonne, $posizione, $larghezzaform);
                    $pdf->MultiCell($width, $rowcount * $h, $valore, $border, $align, $fill, $ln);
                }
            }
            $pdf->Ln();
        }
        $pdf->Cell(0, 10, GrigliaFiltriUtils::traduciFiltri(array('filtri' => $risposta->filtri)), 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
    private function getWidthColumn($modellicolonne, $posizione, $larghezzaform)
    {
        if (isset($modellicolonne[$posizione])) {
            $width = ((297 * $modellicolonne[$posizione]['width']) / $larghezzaform) / 2;
        } else {
            $width = ((297 * 100) / $larghezzaform) / 2;
        }
        return $width;
    }*/
}
