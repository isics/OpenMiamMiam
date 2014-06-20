<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Document;

use TCPDF;

class OpenMiamMiamPDF extends TCPDF
{
    public function Footer() {
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', '', 9);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
} 