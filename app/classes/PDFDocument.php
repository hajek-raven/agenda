<?php
class PDFDocument
{
  protected $documentTemplateFile;
  protected $oddFooterTemplateFile;
  protected $evenFooterTemplateFile;
  protected $oddHeaderTemplateFile;
  protected $evenHeaderTemplateFile;
  protected $template;
  protected $oddFooterTemplate;
  protected $styleFiles = array();

  public function __construct($documentTemplateFile)
  {
    $this->documentTemplateFile = $documentTemplateFile;
    $this->template =  new \Nette\Templating\FileTemplate($documentTemplateFile);
    $this->template->registerFilter(new \Nette\Latte\Engine);
    $this->template->registerHelperLoader('\Nette\Templating\Helpers::loader');
  }

  public function addDefaultStyle()
  {
    $this->styleFiles[] = "css/pdf.css";
  }

  public function addStyle($cssFile)
  {
    $this->styleFiles[] = $cssFile;
  }

  public function render()
  {
    require("../vendor/mpdf/mpdf/mpdf.php");
    $mpdf = new \mPDF('', 'A4', 10, 'arial');
    $mpdf->mirrorMargins = true;
    $mpdf->ignore_invalid_utf8 = true;
    foreach($this->styleFiles as $file)
    {
      $mpdf->WriteHTML(file_get_contents($file),1);
    }
    
  }
}
