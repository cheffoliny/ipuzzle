<?php
/**
 * FDrawing.php
 *--------------------------------------------------------------------
 *
 * Holds the drawing $im
 * You can use get_im() to add other kind of form not held into these classes.
 *
 *--------------------------------------------------------------------
 * Revision History
 * v1.2.3b	31 dec	2005	Jean-S�bastien Goupil	Just one barcode per drawing
 * v1.2.1	27 jun	2005	Jean-S�bastien Goupil	Font support added
 * V1.00	17 jun	2004	Jean-Sebastien Goupil
 *--------------------------------------------------------------------
 * $Id: FDrawing.php,v 1.4 2006/01/06 02:10:45 jsgoupil Exp $
 * PHP5-Revision: 1.6
 *--------------------------------------------------------------------
 * Copyright (C) Jean-Sebastien Goupil
 * http://other.lookstrike.com/barcode/
 */
class FDrawing {
	var $w, $h;		// int
	var $color;		// Fcolor
	var $filename;		// char *
	var $im;		// {object}
	var $barcode;		// BarCode

	/**
	 * Constructor
	 *
	 * @param int $w
	 * @param int $h
	 * @param string filename
	 * @param FColor $color
	 */
	function FDrawing($filename, &$color) {
		$this->filename = $filename;
		$this->color =& $color;
	}

	/**
	 * Destructor
	 */
	//public function __destruct() {
	//	$this->destroy();
	//}

	/**
	 * Init Image and color background
	 */
	function init() {
		$this->im = imagecreatetruecolor($this->w, $this->h)
		or die('Can\'t Initialize the GD Libraty');
		imagefill($this->im, 0, 0, $this->color->allocate($this->im));
	}

	/**
	 * @return resource
	 */
	function &get_im() {
		return $this->im;
	}

	/**
	 * @param resource $im
	 */
	function set_im(&$im) {
		$this->im = $im;
	}

	/**
	 * Add barcode into the drawing array (for future drawing)
	 * ! DEPRECATED !
	 *
	 * @param BarCode $barcode
	 * @deprecated
	 */
	function add_barcode(&$barcode) {
		$this->setBarcode($barcode);
	}

	/**
	 * Set Barcode for drawing
	 *
	 * @param BarCode $barcode
	 */
	function setBarcode(&$barcode) {
		$this->barcode =& $barcode;
	}

	/**
	 * Draw first all forms and after all texts on $im
	 * ! DEPRECATED !
	 *
	 * @deprecated
	 */
	function draw_all() {
		$this->draw();
	}

	/**
	 * Draw the barcode on the image $im
	 */
	function draw() {
		$this->w = $this->barcode->getMaxWidth();
		$this->h = $this->barcode->getMaxHeight();
		$this->init();
		$this->barcode->draw($this->im);
	}

	/**
	 * Save $im into the file (many format available)
	 *
	 * @param int $image_style
	 * @param int $quality
	 */
	function finish($image_style = 'png', $quality = 100) {
		if ($image_style == 'png' ) {
			if (empty($this->filename)) {
				imagepng($this->im);
			} else {
				imagepng($this->im, $this->filename);
			}
		} elseif ($image_style == 'jpg') {
			imagejpeg($this->im, $this->filename, $quality);
		}
	}

	/**
	 * Free the memory of PHP (called also by destructor)
	 */
	function destroy() {
		@imagedestroy($this->im);
	}
};
?>