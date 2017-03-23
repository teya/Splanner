<?php
    // require('../wp-blog-header.php' );
    require('../download-invoice-pdf/fpdf.php');
    include('../wp-load.php'); 
    // define('FPDF_FONTPATH','./font/');
    // require('../download-invoice-pdf/makefont/makefont.php');
    // MakeFont('../download-invoice-pdf/font/Arial.ttf','Arial');
    $invoice_id = $_GET['id'];

    $current_user = wp_get_current_user();

    if(empty($invoice_id)){
        wp_redirect(get_site_url().'/view-invoice/');
        exit(); 
    }

    global $wpdb;
    $invoice_info = $wpdb->get_row('SELECT * FROM '. SPLAN_INVOICE_TBL . ' WHERE id = '.$invoice_id);
    $approved_invoice = $invoice_info->person_approval + $invoice_info->admin_approval;
    $dollar_per_hour = round($invoice_info->salary / $invoice_info->total_hours, 2);


    if($approved_invoice < 2){
        wp_redirect(get_site_url().'/view-invoice/');
        exit();  
    }
  
    if($invoice_info->person_id == $current_user->ID OR $current_user->ID == 2){
 
    }else{
        wp_redirect(get_site_url().'/view-invoice/');
         exit();           
    }

    $client_lists = unserialize($invoice_info->clients_invoices_table);

    if(empty($invoice_info)){
        die('INVOICE ID NOT AVAILABLE!');
    }
    $person_info = $wpdb->get_row('SELECT * FROM '. SPLAN_PERSON_TBL .' WHERE wp_user_id = '. $invoice_info->person_id);


    class PDF extends FPDF
    {
        // private $person_name;

        function setInvoiceInfo($invoice_info){
            $this->invoice_date = $invoice_info->date;
            $this->total_salary = $invoice_info->salary;
            $this->invoice_id = $invoice_info->id;
            $this->date_created = $invoice_info->date_created;

        }
        function setPersonInfo($person_info){
            $this->person_name = $person_info->person_fullname;
            $this->person_address = $person_info->person_address;
            $this->person_contact_no = $person_info->person_mobile;
            $this->email = $person_info->person_paypal_email;
            $this->person_id = $person_info->wp_user_id;

        }
         // Page header
        function Header()
        {
            $dates = explode("-", $this->invoice_date);
            $y = substr($dates[1], -2);
            // Logo
            $this->Image(get_template_directory_uri().'/images/invoice-pc-icon.png',10,6,30);
            // $this->AddFont('Arial','','Arial.php');
            // Arial bold 15
            $this->SetFont('Arial','B',15);
            // Move to the right
            $this->Cell(35);
            // Title
            $this->Cell(0,8,$this->person_name,0,10,'L');
            //Person Address
            $this->SetFont('Arial','',8);
            $this->Cell(0,5,preg_replace( "/\r|\n/", "", $this->person_address) ,0,10,'L');
            $this->Cell(0,5,'Contact Number: '.$this->person_contact_no,0,10,'L');
            $this->Cell(0,5,'TIN: ',0,10,'L');
            $this->SetXY(135, 30);
            $this->SetTextColor(244, 66, 66);
            $this->Cell(20,0,'Paypal Email: '.$this->email,0,10,'L');
            $this->SetTextColor(0, 0, 0);
            $this->SetXY(15,40);
            $this->SetFont('Arial','B',8);
            $this->Cell(20,0,'Billed to: ',0,10,'L');
            $this->SetXY(135,40);
            $this->SetFont('Arial','B',8);
            $this->Cell(20,0,'Invoice No: ','L');
            $this->SetXY(153,40);
            $this->SetFont('Arial','U',8);
            $this->Cell(20,0,'   '.$y.''.$dates[0].''.$this->person_id.'                         ',0,10,'L');
            $this->SetXY(135,45);
            $this->SetFont('Arial','B',8);
            $this->Cell(20,0,'Salary for: ',0,10,'L');
            $this->SetXY(150,45);
            $this->SetFont('Arial','U',8);
            $this->Cell(20,0,date("M", mktime(0, 0, 0, $dates[0], 10)).'-'.$dates[1].'                           ',0,10,'L');

            $this->SetXY(135,50);
            $this->SetFont('Arial','B',8);
            $this->Cell(20,0,'Date: ',0,10,'L');
            $this->SetXY(145,50);
            $this->SetFont('Arial','U',8);
            $this->Cell(20,0,$this->date_created.'                           ',0,10,'L');


            $this->SetXY(30,40);
            $this->SetFont('Arial', 'U',8);
            $this->Cell(100,0,'SEOWEB Solutions                                                         ',0,10,'L');
            $this->SetXY(15,45);
            $this->SetFont('Arial','B',8);
            $this->Cell(20,0,'Address: ',0,10,'L');
            $this->SetXY(30,45);
            $this->SetFont('Arial', 'U',8);
            $this->Cell(100,0,iconv('UTF-8','windows-1252','Gärdsåsgatan 55A                         '),0,10,'L');
            $this->SetXY(30,50);
            $this->SetFont('Arial', 'U',8);
            $this->Cell(100,0,'415 16 Gothenburg, Sweden                                           ',0,10,'L');
            // Line break
            // $this->Ln(20);
        }
        // Page footer
        function Footer()
        {
            // Position at 1.5 cm from bottom
            $this->SetY(-15);
            // Arial italic 8
            $this->SetFont('Arial','I',8);
            // Page number
            // $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
        }
    }
    $row = 72;
    // Instanciation of inherited class
    $pdf = new PDF();
    $pdf->setInvoiceInfo($invoice_info);
    // $pdf->AddFont('Arial','','Arial.php');
    $pdf->setPersonInfo($person_info);
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetTitle('Auto Invoice: '.$person_info->person_fullname.'-'.date("F", mktime(0, 0, 0, $dates[0], 10)).'-'.$dates[1]);
    $pdf->SetFillColor(175, 175, 175);
    // $pdf->SetFont('Arial','B',8);
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY(15,65);
    $pdf->Cell(60,7,'Client ',1,10,'C');
    $pdf->SetXY(75,65);
    $pdf->Cell(45,7,'Projects',1,10,'C');
    $pdf->SetXY(120,65);
    $pdf->Cell(20,7,'#Hours '.date("M", mktime(0, 0, 0, $dates[0], 10)),1,10,'C');
    $pdf->SetXY(140,65);
    $pdf->Cell(20,7,'$/h',1,10,'C');
    $pdf->SetXY(160,65);
    $pdf->Cell(25,7,'Total',1,10,'C');
    // $pdf->SetFont('Arial','',8);

    foreach($client_lists as $client){
        $tolal_column = (!empty($client['total']))? $client['total'] : number_format($client['total_hours'] * $dollar_per_hour,2);
        $pdf->SetXY(15,$row);
        $pdf->Cell(60,7,iconv('UTF-8','windows-1252',$client['clientname']),1,10,'L');

        $pdf->SetXY(75,$row);
        $pdf->Cell(45,7,iconv('UTF-8','windows-1252',$client['project_name']),1,10,'C');

        $pdf->SetXY(120,$row);
        $pdf->Cell(20,7,substr(convertTime($client['total_hours']), 0, -3),1,10,'R');   

        $pdf->SetXY(140,$row);
        $pdf->Cell(20,7,$dollar_per_hour ,1,10,'R'); 

        $pdf->SetXY(160,$row);
        $pdf->Cell(25,7,$tolal_column,1,10,'R'); 
          
        $row = $row + 7;

    }

    $pdf->SetXY(150,$row);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(25,7,'Sub Total:',0,10,'C');

    $pdf->SetXY(169,$row);
    $pdf->SetFont('Arial','U',10);
    $pdf->Cell(25,7,$invoice_info->salary . ' USD',0,10,'C');

    $pdf->SetXY(150,$row + 7);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(25,7,'Total Hours:',0,10,'C');

    $pdf->SetXY(169,$row + 7);
    $pdf->SetFont('Arial','U',10);
    $pdf->Cell(25,7,$invoice_info->total_hours . ' ',0,10,'C');

    $pdf->Output();
    // $pdf->Output('report.pdf', 'F');


?>