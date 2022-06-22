<?php
// Michael Rajotte - 2016 June
// fluid.box.php
// Fluid bin packing box class.
// Uses DVdoug/BoxPacker. Thanks doug :)

namespace DVDoug\BoxPacker;
require_once (__DIR__ . "/3rd-party-src/packing-api/vendor/autoload.php");

class FluidBox implements Box {
	// Weight is in grams, dimensions are in millimeters.
    public function __construct(
        $reference,
        $outerWidth,
        $outerLength,
        $outerDepth,
        $emptyWeight,
        $innerWidth,
        $innerLength,
        $innerDepth,
        $maxWeight
    ) {
        $this->reference = $reference;
        $this->outerWidth = $outerWidth;
        $this->outerLength = $outerLength;
        $this->outerDepth = $outerDepth;
        $this->emptyWeight = $emptyWeight;
        $this->innerWidth = $innerWidth;
        $this->innerLength = $innerLength;
        $this->innerDepth = $innerDepth;
        $this->maxWeight = $maxWeight;
        $this->innerVolume = $this->innerWidth * $this->innerLength * $this->innerDepth;
    }

    public function getReference() {
        return $this->reference;
    }

    public function getOuterWidth() {
        return $this->outerWidth;
    }

    public function getOuterLength() {
        return $this->outerLength;
    }

    public function getOuterDepth() {
        return $this->outerDepth;
    }

    public function getEmptyWeight() {
        return $this->emptyWeight;
    }

    public function getInnerWidth() {
        return $this->innerWidth;
    }

    public function getInnerLength() {
        return $this->innerLength;
    }

    public function getInnerDepth() {
        return $this->innerDepth;
    }

    public function getInnerVolume() {
        return $this->innerVolume;
    }

    public function getMaxWeight() {
        return $this->maxWeight;
    }
}

class FluidItem implements Item {
	// Weight is in grams, dimensions are in millimeters.
    public function __construct($description, $width, $length, $depth, $weight, $keepFlat, $price, $fluid_item_array) {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;
        $this->price = $price;
        $this->fluid_item = $fluid_item_array;

        $this->volume = $this->width * $this->length * $this->depth;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getWidth() {
        return $this->width;
    }

    public function getLength() {
        return $this->length;
    }

    public function getDepth() {
        return $this->depth;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function getVolume() {
        return $this->volume;
    }

    public function getKeepFlat() {
        return $this->keepFlat;
    }

    public function getPrice() {
		return $this->price;
	}

	public function getItem() {
		return $this->fluid_item;
	}
}

class FluidShipping {
	public $fluid_boxes;

	protected $packer;
	private $f_cart;

	public function __construct ($f_cart, $f_boxes) {
		$this->packer = new Packer ();

		// Name, OuterWidth, OuterLength, OuterDepth, EmptyWeight, InnerWidth, InnerLength, InnerDepth, MaxWeight
		// Boxes are in (mm) for dimensions and (g) for weight.
		foreach($f_boxes as $f_box) {
			$this->packer->addBox(new FluidBox($f_box['b_name'], $f_box['b_outer_width'], $f_box['b_outer_length'], $f_box['b_outer_depth'], $f_box['b_empty_weight'], $f_box['b_inner_width'], $f_box['b_inner_length'], $f_box['b_inner_depth'], $f_box['b_max_weight']));
		}

		$this->f_cart = $f_cart;
		$this->fluid_boxes = NULL;

		$this->php_pack_boxes();
	}

	private function php_pack_boxes() {
		foreach($this->f_cart as $cart) {
			for($i = 0; $i < $cart['p_qty']; $i++) {
				// p_weight * 1000 to convert kilograms to grams. Shipping modules of CanadaPost and FedEx use kilograms which get converted after again.
				// convert dimensions from cm to millimeters. * 10. for the items before packing.
				$this->packer->addItem(new FluidItem($cart['m_name'] . " " . $cart['p_name'], $cart['p_width'] * 10, $cart['p_length'] * 10, $cart['p_height'] * 10, $cart['p_weight'] * 1000, false, $cart['p_price'], $cart));
			}
		}

		$f_packed_boxes = $this->packer->pack();

		foreach($f_packed_boxes as $key => $f_packed_box) {
			$f_box_type = $f_packed_box->getBox(); // A box object.

			$this->fluid_boxes[$key] = Array("f_box_type" => $f_box_type->getReference(), "length" => ceil($f_box_type->getOuterWidth() / 10), "width" => ceil($f_box_type->getOuterLength() / 10), "height" => ceil($f_box_type->getOuterDepth() / 10), "weight" => $f_packed_box->getWeight() / 1000, "girth" => (($f_box_type->getOuterDepth() / 10) * 2) + (($f_box_type->getOuterLength() / 10) * 2), "l_girth" => ($f_box_type->getOuterWidth() / 10) + (($f_box_type->getOuterDepth() / 10) * 2) + (($f_box_type->getOuterLength() / 10) * 2), "price" => 0, "f_items" => NULL);

			$f_items_in_box = $f_packed_box->getItems();

			foreach($f_items_in_box as $item) {
				$this->fluid_boxes[$key]['items'][] = $item->getItem();
				$this->fluid_boxes[$key]['price'] = $this->fluid_boxes[$key]['price'] + $item->getPrice();
				//$this->fluid_boxes[$key]['b_items'][] = $item;
			}
		}
	}

	public function php_fluid_packed_boxes() {
		return $this->fluid_boxes;
	}
}
?>
