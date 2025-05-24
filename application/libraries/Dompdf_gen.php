<?php
require_once APPPATH . 'libraries/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class Dompdf_gen
{
	function __construct()
	{
		$options = new Options();
        $options->set('defaultFont', 'Courier');
        $this->dompdf = new Dompdf($options);
	}

    public function loadHtml($html) {
        $this->dompdf->loadHtml($html);
    }

    public function setPaper($size, $orientation) {
        $this->dompdf->setPaper($size, $orientation);
    }

    public function render() {
        $this->dompdf->render();
    }

    public function stream($filename, $options = []) {
        $this->dompdf->stream($filename, $options);
    }

    public function save($filename) {
        $output = $this->dompdf->output();
        file_put_contents($filename, $output);
    }
}
?>
    