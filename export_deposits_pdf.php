<?php
require('fpdf/fpdf.php');
include 'database.php';

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'Deposit Transactions Report',0,1,'C');
        $this->Ln(5);
        $this->SetFont('Arial','B',10);
        $this->Cell(30,10,'User',1);
        $this->Cell(40,10,'Transaction ID',1);
        $this->Cell(25,10,'Amount',1);
        $this->Cell(20,10,'Currency',1);
        $this->Cell(25,10,'Status',1);
        $this->Cell(45,10,'Date',1);
        $this->Ln();
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

$stmt = $pdo->query("
    SELECT t.*, u.name
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdf->Cell(30,10, $row['name'],1);
    $pdf->Cell(40,10, $row['transaction_id'],1);
    $pdf->Cell(25,10, number_format($row['amount'],2),1);
    $pdf->Cell(20,10, $row['currency'],1);
    $pdf->Cell(25,10, $row['status'],1);
    $pdf->Cell(45,10, $row['created_at'],1);
    $pdf->Ln();
}

$pdf->Output('D', 'deposit_report.pdf');
?>
