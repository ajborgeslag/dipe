<?php

class Excel {

    // Se desactivan los mensajes de debug
    /*error_reporting(~(E_WARNING|E_NOTICE));*/
    //error_reporting(E_ALL);

    // Se especifica la zona horaria
    public function export_product_report($column_data)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', TRUE);
        ini_set('display_startup_errors', TRUE);
        date_default_timezone_set('America/Mexico_City');

        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

        require_once dirname(__FILE__) . '/Classes/PHPExcel.php';

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Dipepsa admin")
            ->setLastModifiedBy("Dipepsa admin")
            ->setTitle('Productos')
            ->setSubject('Reportes de productos');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);



        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Clave interna');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(12);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B1', 'Descripción');
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(12);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('C1', 'Impuesto');
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setSize(12);


        if($column_data!=false)
        {
            $indice = 2;
            foreach ($column_data as $data)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$indice, $data['clave_interna']);
                $objPHPExcel->getActiveSheet()->getStyle('A'.$indice)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->getActiveSheet()->setCellValue('B'.$indice, $data['product_name']);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$indice)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$indice, $data['tax_percentage']);
                $objPHPExcel->getActiveSheet()->getStyle('C'.$indice)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $indice = $indice+1;
            }
        }

        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="product_report.xls"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        return true;
    }










}