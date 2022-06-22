<?php
// Michael Rajotte - 2016 Aout
// fluid.mode.class.php
// A class for instructing fluid.loader manager if it is to display category, manufacturer or item listing modes.

class Fluid_Mode {
	public $X;
	public $X_id;
	public $id;
	public $X_enable;
	public $enable;
	public $name;
	public $desc;
	public $sortorder;
	public $seo;
	public $filters;
	public $filters_opp;
	public $images;
	public $prod_filters;
	public $prod_filters_opp;
	
	public $mode_name;
	public $mode_name_real;
	public $mode_name_real_cap;
	public $breadcrumb;
	public $msg_no_products;
	public $table;
	public $table_filter_keys;
	
	public $p_catmfg_id;
	public $p_catmfg_id_opp;
	public $p_catmfg_object;
	public $sort_order;
	public $mode;
				
	public function __construct ($mode) {
		if($mode == "manufacturers") {
			$var = "m";
			$var_opposite = "c";
			$this->table = TABLE_MANUFACTURERS;
			$this->table_filter_keys = TABLE_FILTER_KEYS_MANUFACTURERS;
			$this->mode_name = "Category";
			$this->mode_name_real = "manufacturer";
			$this->mode_name_real_cap = "Manufacturer";
			$this->breadcrumb = "Manufacturers";
			$this->images = $var . "_images";
			$this->msg_no_products = "No items for this manufacturer.";
			
			$this->p_catmfg_id = "p_mfgid";
			$this->p_catmfg_id_opp = "p_catid";
			$this->p_catmfg_object = "p_manufacturer";
			$this->sort_order = "_mfg";
		}
		else if($mode == "items") {
			$var = "c";
			$var_opposite = "m";
			$this->table = TABLE_CATEGORIES;
			$this->table_filter_keys = TABLE_FILTER_KEYS_CATEGORIES;
			$this->mode_name = "Manufacturer";
			$this->mode_name_real = "category";
			$this->mode_name_real_cap = "Category";
			$this->images = $var . "_image";
			$this->breadcrumb = "Categories";
			$this->msg_no_products = "No items found.";
			
			$this->p_catmfg_id = "p_catid";
			$this->p_catmfg_id_opp = "p_mfgid";
			$this->p_catmfg_object = "p_category";
			$this->sort_order = "";		
		}
		else if($mode == "orders") {
			$var = "s";
			$var_opposite = "si";
			$this->table = TABLE_SALES;
			$this->table_filter_keys = "";
			$this->mode_name = "Orders";
			$this->mode_name_real = "orders";
			$this->mode_name_real_cap = "Orders";
			$this->images = $var . "_image";
			$this->breadcrumb = "Orders";
			$this->msg_no_products = "No orders found.";
			
			$this->p_catmfg_id = "";
			$this->p_catmfg_id_opp = "";
			$this->p_catmfg_object = "";
			$this->sort_order = "";		
		}
		else if($mode == "banners") {
			$var = "b";
			$var_opposite = "bi";
			$this->table = TABLE_BANNERS;
			$this->table_filter_keys = "";
			$this->mode_name = "Banners";
			$this->mode_name_real = "banners";
			$this->mode_name_real_cap = "banners";
			$this->images = $var . "_image";
			$this->breadcrumb = "Banners";
			$this->msg_no_products = "No banners found.";
			
			$this->p_catmfg_id = "";
			$this->p_catmfg_id_opp = "";
			$this->p_catmfg_object = "";
			$this->sort_order = "";		
		}
		else if($mode == "accounts") {
			$var = "u";
			$var_opposite = "ui";
			$this->table = TABLE_USERS;
			$this->table_filter_keys = "";
			$this->mode_name = "Accounts";
			$this->mode_name_real = "accounts";
			$this->mode_name_real_cap = "accounts";
			$this->images = $var . "_image";
			$this->breadcrumb = "Accounts";
			$this->msg_no_products = "No accounts found.";
			
			$this->p_catmfg_id = "";
			$this->p_catmfg_id_opp = "";
			$this->p_catmfg_object = "";
			$this->sort_order = "";		
		}
		else if($mode == "feedback") {
			$var = "f";
			$var_opposite = "fi";
			$this->table = TABLE_FEEDBACK;
			$this->table_filter_keys = "";
			$this->mode_name = "Feedback";
			$this->mode_name_real = "feedback";
			$this->mode_name_real_cap = "feedback";
			$this->images = $var . "_image";
			$this->breadcrumb = "Feedback";
			$this->msg_no_products = "No feedback found.";
			
			$this->p_catmfg_id = "";
			$this->p_catmfg_id_opp = "";
			$this->p_catmfg_object = "";
			$this->sort_order = "";		
		}		
		else if($mode == "logs") {
			$var = "l";
			$var_opposite = "li";
			$this->table = TABLE_LOGS;
			$this->table_filter_keys = "";
			$this->mode_name = "Logs";
			$this->mode_name_real = "logs";
			$this->mode_name_real_cap = "logs";
			$this->images = $var . "_image";
			$this->breadcrumb = "Accounts";
			$this->msg_no_products = "No logs found.";
			
			$this->p_catmfg_id = "";
			$this->p_catmfg_id_opp = "";
			$this->p_catmfg_object = "";
			$this->sort_order = "";		
		}									
		else {
			$var = "c";
			$var_opposite = "m";			
			$this->table = TABLE_CATEGORIES;
			$this->mode_name = "Manufacturer";
			$this->mode_name_real = "category";
			$this->mode_name_real_cap = "Category";
			$this->images = $var . "_image";
			$this->breadcrumb = "Categories";
			$this->msg_no_products = "No items in this category.";
			
			$this->p_catmfg_id = "p_catid";
			$this->p_catmfg_id_opp = "p_mfgid";
			$this->p_catmfg_object = "";
			$this->sort_order = "";
		}
		
		$this->mode = $mode;
		$this->id = $var . "_id";
		$this->X = $var;	
		$this->X_id = $var . "." . $var . "_id";
		$this->X_enable = $var . "." . $var . "_enable";
		$this->enable = $var . "_enable";
		$this->name = $var . "_name";
		$this->weight = $var . "_search_weight";
		$this->google_cat_id = $var . "_google_cat_id";
		$this->keywords = $var . "_keywords";
		$this->desc = $var . "_desc";
		$this->seo = $var . "_seo";
		$this->filters = $var . "_filters";
		$this->filters_opp = $var_opposite . "_filters";		
		$this->prodfilters = "p_" . $var . "_filters";
		$this->prodfilters_opp = "p_" . $var_opposite . "_filters";
		$this->sortorder = $var . "_sortorder";
		$this->formula_status = $var . "_formula_status";
		$this->formula_math = $var . "_formula_math";
	}
}

?>
