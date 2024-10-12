<?php
require("pdf.php");

function generateOVRF(
    $name,          /*Student's full name*/
    $gender,        /*Male/Female*/
    $studentType,   /*Regular/Irregular*/
    $semester,      /*1st/2nd*/
    $year,          /*YYYY-YYYY*/
    $studentID,     /*Student ID*/
    $course,        /*Course/Major*/
    $yearLevel,     /*1st/2nd/3rd/4th*/
    $subjects,      /*An array of: ["Subject", ""Subject title", "Section/Room", "Units", "Total Units", "Units", "Rate/Unit", "Total subject fee"]*/
    $feeDetails,    /*An array of: ["Fee name", "Fee"]*/   
    $paymentDetails,/* ["PAYMENT MODE", "Mode"], ["ACCOUNT PAID", "Paid"], ["DATE PAID", "Date"]*/
    $oldAccounts,   /*An array of: ["Assessment", "Amount"]*/
    $registrarRepresentee /*Registrar's representee*/
) {
    $pdf = new PDF();

    $pdf->AddPage();

    $pdf->setFont('Arial', '', 6);
    $pdf->Cell(0, 4, $semester . " Semester " . "AY " . $year, 0, 1, 'C');

    $pdf->SetWidths([45, 45, 40, 23, 27]);
    $pdf->FancyRow(
        ["", "", "", "Date and time printed: ", date("m/d/Y h:i A")],
        ["0", "0", "0", "0", "0"],
        ["L", "L", "L", "L", "R"]
    );

    $pdf->SetWidths([25, 65, 25, 65]);
    $pdf->FancyRow(
        ["Student ID", $studentID, "Course/Major", $course],
        ["0", "0", "0", "0"],
        ["L", "L", "L", "L"],
        ["", "B", "", "B"]
    );

    $pdf->SetWidths([25, 65, 15, 30, 15, 30]);
    $pdf->FancyRow(
        ["Name", $name, "Year level", $yearLevel, "Gender", $gender],
        ["0", "0", "0", "0", "0", "0"],
        ["L", "L", "L", "L", "L", "L"],
        ["", "B", "", "B", "", "B"]
    );

    $pdf->SetWidths([25, 155]);
    $pdf->FancyRow(["Student Type", $studentType],
        ["0", "0"], ["L", "L"],
        ["", "B"]
    );

    $pdf->SetWidths([25, 65, 35, 55]);
    $pdf->FancyRow(
        ["Students's Signature ", "______________________", "Parent's/Guardian's Signature", "______________________"], 
        ["0", "0", "0", "0"],
        ["L", "L", "L", "L"],  
        ["", "", "", ""]
    );

    $pdf->Ln(5);

    $pdf->SetWidths([15, 50, 20, 15, 15, 15, 15, 15, 20]);
    $pdf->FancyRow(
        ["SUBJECT", "SUBJECT TITLE", "SCHEDULE", "SECTION / ROOM #", "LEC/LAB UNITS", "TOTAL UNITS", "UNITS TAKEN", "RATE/UNIT", "TOTAL SUBJECT FEE"],
        ["1", "1", "1", "1", "1", "1", "1", "1", "1"],
        ["C", "C", "C", "C", "C", "C", "C", "C", "C"],
        ["", "", "", "", "", "", "", "", ""]
    );

    $totalUnits = 0;
    foreach ($subjects as $subject) {
        $totalUnits += $subject[5];
        $pdf->FancyRow(
            $subject,
            ["1", "1", "1", "1", "1", "1", "1", "1", "1"],
            ["C", "C", "C", "C", "C", "C", "C", "C", "C"],
            ["", "", "", "", "", "", "", "", ""]
        );
    }

    $pdf->SetWidths([115, 15, 15, 15, 20]);
    $pdf->FancyRow(["TOTAL LOAD UNITS", $totalUnits, "", "", ""],
        ["1", "1", "1", "1", "1"],
        ["R", "C", "L", "L", "L"]
    );

    $pdf->Ln(5);

    $pdf->SetWidths([90, 90]);
    $pdf->FancyRow(["=FEE DETAILS=", "=PAYMENT DETAILS="],
        ["0", "0"],
        ["C", "C"]
    );

    $pdf->SetWidths([45, 45]);
    $combinedPaymentDetails = [];
    foreach ($paymentDetails as $payment) {
        $combinedPaymentDetails[] = $payment;
    }

    $totalFee = 0;
    foreach ($feeDetails as $fee) {
        $cleanedFee = preg_replace('/[^\d.]/', '', $fee[1]);
        $totalFee += (float)$cleanedFee;
    }

    $formattedTotalFee = "Php " . number_format($totalFee, 2);

    $combinedPaymentDetails[] = ["Account paid", $formattedTotalFee];
    $combinedPaymentDetails[] = ["(Business Office) Receipt printed by: ", ""];

    $pdf->SetWidths([45, 45, 45, 45]);

    $maxRows = max(count($feeDetails), count($combinedPaymentDetails));
    for ($i = 0; $i < $maxRows; $i++) {
        $fee = isset($feeDetails[$i]) ? $feeDetails[$i] : ["", ""];
        
        if (isset($fee[1])) {
            $cleanedFee = preg_replace('/[^\d.]/', '', $fee[1]);
            $formattedFee = "Php " . number_format((float)$cleanedFee, 2);
            $fee[1] = $formattedFee;
        }

        $payment = isset($combinedPaymentDetails[$i]) ? $combinedPaymentDetails[$i] : ["", ""];
        $pdf->FancyRow(
            array_merge($fee, $payment),
            ["0", "0", "0", "0"],
            ["L", "R", "L", "R"],
            ["", "B", "", "B"]
        );
    }

    $pdf->SetFont("Arial","",6);
    $pdf->SetXY(115, 180); 
    $pdf->MultiCell(70, 5, "(NOTE: Above installment schedule may change based on actual payment and after enrolment adjustments.)", 0, "C");

    $pdf->SetXY(15, 222);/*this should be adjusted when modifying the layout above it; poor solution*/
    $pdf->Cell(0, 5, "___________________________________________________________________________",0, "C");

    $pdf->SetWidths([45, 45]);

    $pdf->FancyRow(
        ["TOTAL ASSESSMENTS", $formattedTotalFee],
        ["0", "0"],
        ["L", "R"],
        ["", "B"]
    );

    $pdf->FancyRow(
        ["OLD ACCOUNTS", "Php " . number_format($oldAccounts, 2)],
        ["0", "0"],
        ["L", "R"],
        ["", "B"]
    );

    $pdf->Ln(5);

    $pdf->SetWidths([45, 45]);
    $pdf->FancyRow(
        ["Student load verified & confirmed by: ", "___________________________________"],
        ["0", "0"],
        ["L", "R"],
        ["", "B"]
    );

    $pdf->SetX(49);
    $pdf->SetFont("Arial","",6);
    $pdf->MultiCell(70, 5, $registrarRepresentee, 0, "C");

    $pdf->SetX(49);
    $pdf->SetFont("Arial","I",6);
    $pdf->MultiCell(70, 5, "Registrar", 0, "C");

    $pdf->SetXY(110, 225);
    $pdf->SetTextColor(169,169,169);
    $pdf->SetFont("Arial","",20);
    $pdf->Cell(70, 5, "OFFICIALLY ENROLLED", 0, "C");

    $outputPath = '../ovrf/';
    if (!file_exists($outputPath)) mkdir($outputPath, 0777, true);
    $pdf->Output('F', $outputPath . $name . ' - ovrf.pdf');
}
?>