<?php

/**
 * Template Name: addproduct
 * Template Post Type: page
 * 
 */


// Get the woocommerce api functions
require "woocommerce-api.php";
require  dirname(plugin_dir_path(__FILE__)) . "/includes/helpers.php";

// get the correct protocol
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
$link = "https";
else $link = "http";
$link = sanitize_url($link . "://" . $_SERVER['HTTP_HOST']);

// Global variables for the pages
$login_page = "wp-smart-login";
$products_page = "wp-smart-products";
$add_page = "wp-smart-add-product";
$validCode = smt_smart_commerce_checkCode();
wp_enqueue_media();




// Check where the request for the current page is coming from
if(isset($_SERVER['HTTP_REFERER'])){
    $previous_page = sanitize_url($_SERVER['HTTP_REFERER']);
    $from_products = preg_match("/" . $products_page . "\/\?id=[1-9]{1,5}/", $previous_page);
    $from_self = preg_match("/" . $add_page ."/", $previous_page);

    if($from_products || $from_self){
        if($validCode){
            $categoriesData = json_decode($smt_smart_commerce_listCategories(), true);
            $categories = $categoriesData['data'];

            $unitData = json_decode($smt_smart_commerce_units(), true);            

            $weightUnit;
            $dimensionsUnit;

            foreach($unitData as $option){
                if($option['id'] == "woocommerce_weight_unit"){
                    $weightUnit = $option['value'];
                }
                if($option['id'] == "woocommerce_dimension_unit"){
                    $dimensionsUnit = $option['value'];
                }
            }

            $taxClassData = json_decode($smt_smart_commerce_getTaxClasses(), true);

            $shippingClasses = json_decode($smt_smart_commerce_getShippingClasses(), true);
        }
        

    }
    else {
        // Add product to test on local
        header("Location: " . $link . "/" . $login_page);
        exit;
    }
}
else {
    // Add product to test on local
    header("Location: " . $link . "/" . $login_page);
    exit;
}

?>

<?php
    // This will happen when the form is submitted
    if(isset($_POST['save'])){
        $error = "";

        $data = [];
        $data['name'] = sanitize_text_field($_POST['product-name']);

        $priceRegex = "/^[0-9]{1,9}(\.[0-9]{1,3})?$/";

        if(isset($_POST['product-regular-price'])){
            $regular_price = sanitize_text_field($_POST['product-regular-price']);
            if(preg_match($priceRegex, $regular_price)) $data['regular_price'] = $regular_price;
        } 

        if(isset($_POST['product-sale-price'])){
            $sale_price = sanitize_text_field($_POST['product-sale-price']);
            if(preg_match($priceRegex, $sale_price)) $data['sale_price'] = $sale_price;
        }

        if(isset($_POST['tax-class'])){
            $taxClass = sanitize_text_field($_POST['tax-class']);
            $correctClass = array_filter($taxClassData, function($class) use ($taxClass){ return $class['slug'] == $taxClass;});
            if(count($correctClass) == 1) $data['tax_class'] = $taxClass;
        }

        if(isset($_POST['wp-commerce-product-short-description'])){
            $shortDescription = wp_kses_post($_POST['wp-commerce-product-short-description']);
            $data['short_description'] = $shortDescription;
        } 

        if(isset($_POST['product-sku'])){
            $productSKU = sanitize_text_field($_POST['product-sku']);
            $data['sku'] = $productSKU;
        } 


        // Handle the image uploads
        if($_POST['image-urls']){
            $imageRegex = "/[0-9;]*/";
            $featuredRegex = "/[0-9]*/";
            if(preg_match($imageRegex, $_POST['image-urls'])) $imageArray = explode(";", sanitize_text_field($_POST['image-urls']));
            if(preg_match($featuredRegex, $_POST['featured'])) $featuredImage = sanitize_key($_POST['featured']);

            if($featuredImage !== $imageArray[0]){
                $featuredIndex = array_search($featuredImage, $imageArray);
                array_splice($imageArray, $featuredIndex, 1);
                array_unshift($imageArray, $featuredImage);
            };

            $imageArray = array_map(function($item){return array('id' => sanitize_key($item));}, $imageArray);
            $data['images'] = $imageArray;

        }

       if($validCode) {

            $saveProduct = json_decode($smt_smart_commerce_addProduct($data), true);
            if(isset($saveProduct['error'])) $error = $saveProduct['message'];  
       }

       if(!$error)  header("Location: " . $link . "/" . $products_page . "?id=1");

    }

    $color = get_option("smt_smart_commerce_brand_color") ? get_option("smt_smart_commerce_brand_color") : "#21759b";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo esc_url(get_site_icon_url()) ?>">
    <?php wp_head() ?>
    <title>Add Product</title>
</head>
<body>
    <header>
        <?php if(get_option("smt_smart_commerce_logo_url") !== null): ?>
            <div>
                <img src="<?php echo esc_url(get_option("smt_smart_commerce_logo_url"))?>"/>
            </div>
        <?php endif;?>
        <a href="<?php echo esc_url($link . "/" . $products_page . "?id=1")?>">Go back to products</a>
    </header>
    <?php if($validCode): ?>
        <?php if($error):?>
            <div id="errors"><?php echo esc_html($error) ?></div>
        <?php endif;?>
        <form enctype="multipart/form-data" action="" method="post" enctype='multipart/form-data' id="addeditproduct-form">

            <!-- Name Input field -->
            <div id="title-price" class="flex-fields">
                <div>
                    <label for="product-name" class="label-block">Product Name</label>
                    <input type="text" name="product-name" id="name" value="<?php if(isset($_POST['product-name'])) echo esc_attr(htmlentities($_POST['product-name']))?>">
                </div>
            </div>


            <!-- Image Input Field -->
            <div id="product-image">
                <div>
                    <label for="" class="label-block">Product Image</label>
                    <button type="button" id="image-selector">Select Image</button>
                </div>
                
                <div id="image-viewer"></div>
                <input type="hidden" id="image-urls" name="image-urls">
            </div>

            <!-- General -->
            <div id="general">
                <label class="label-block">General</label>
                <div class="flex-container">
                    <div class="flex-container-vertical">
                        <label for="product-regular-price" class="">Regular Price</label>
                        <input type="text" name="product-regular-price" id="regular-price" value="<?php if(isset($_POST['product-regular-price'])) echo esc_attr(htmlentities($_POST['product-regular-price'])) ?>">
                    </div>

                    <div class="flex-container-vertical">
                        <label for="product-sale-price" class="padding-left">Sale Price</label>
                        <input type="text" name="product-sale-price" id="sale-price" value="<?php if(isset($_POST['product-sale-price'])) echo esc_attr(htmlentities($_POST['product-sale-price'])) ?>">
                    </div>

                    <div class="flex-container-vertical">
                        <label for="tax-class">Tax Class</label>
                        <select name="tax-class" id="tax-class">
                            <option value="" disabled selected>Select Tax Class</option>
                            <?php 
                            
                                foreach($taxClassData as $taxClass){ ?>
                                    <option value="<?php echo esc_attr($taxClass["slug"]) ?>"><?php echo esc_html($taxClass['name'])?></option>
                               <?php }
                            
                            ?>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Inventory -->
            <div id="inventory">
                <label class="label-block">
                    Inventory
                    <span class="help">
                        <i class="fa-regular fa-circle-question"></i>
                        <div>Set stock data like the SKU, whether you want to keep count of stock and if so, how much stock you have.</div>
                    </span> 
                </label>
                <div class="inventory-container">
                    <div id="sku" class="flex-container">
                        <label for="product-sku" class="">SKU</label>
                        <input type="text" name="product-sku" id="sku-input" value="<?php if(isset($_POST['product-sku'])) echo esc_attr(htmlentities($_POST['product-sku'])) ?>">
                    </div>
                </div>

            </div>

            <div id="product-short-description">
                <label for="product-short-description" class="label-block">Short Description</label>
                <?php wp_editor("", "wp-commerce-product-short-description")?>
            </div>

        

            <div id="btn-save">
                <input type="submit" id="save-btn" value="Save" name="save">
            </div>
            <?php //This will get the data from the form to submit to the api?>
            <input type="hidden" name="product-categories" id="hidden-categories">
            <input type="hidden" name="product-tags" id="hidden-tags">
            <input type="hidden" id="brand-color" value="<?php echo esc_html($color)?>">
            <?php // This will pass the data to javascript to handle the displaying of the categories?>
            <input type="hidden" id="php-categories-data" value='<?php echo esc_html(json_encode($categories))?>'>
        </form>
    <?php else: ?>
        <h1>Please enter the required codes in the WP Smart Commerce plugin.</h1>
    <?php endif; ?>

</body>
<?php wp_footer(); ?>
</html>