<?php
$dbconfig = require_once dirname(__FILE__)."/app/config/parameters.php";

$mysqli = new mysqli(
    $dbconfig['parameters']['database_host'],
    $dbconfig['parameters']['database_user'],
    $dbconfig['parameters']['database_password'],
    $dbconfig['parameters']['database_name']
);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}


$prefix = $dbconfig['parameters']['database_prefix'];


$message = '';
$countUpdates = 0;

if (isset($_POST["upload"])) {
    if ($_FILES['product_file']['name']) {
        $filename = explode(".", $_FILES['product_file']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['product_file']['tmp_name'], "r");
            while ($data = fgetcsv($handle)) {
                $reference = mysqli_real_escape_string($mysqli, $data[0]);
                $new_price = mysqli_real_escape_string($mysqli, $data[1]);

                // SIMPLE PRODUCTS
                $simpleProduct = "
                    UPDATE ".$prefix."product 
                    SET price = $new_price
                    WHERE reference = '$reference' ";
                $mysqli->query($simpleProduct);
                $getProdInfo = "SELECT id_product FROM ".$prefix."product WHERE reference = '$reference' ";
                if ($mysqli->affected_rows > 0) {
                    $countUpdates += 1;
                    $prodInfo = $mysqli->query($getProdInfo);
                    //Succesfully update a simple price now for multistores
                    while ($row = $prodInfo->fetch_assoc()) {
                        $tmpId = $row["id_product"];
                        $simpleProductMultistore = "
                            UPDATE ".$prefix."product_shop 
                            SET price = $new_price
                            WHERE id_product = '$tmpId' ";
                        $mysqli->query($simpleProductMultistore);
                    }
                }

                // COMBINATION PRODUCTS
                $combiIdProd = "SELECT id_product, id_product_attribute FROM ".$prefix."product_attribute WHERE reference = '$reference' ";
                $idProd = $mysqli->query($combiIdProd)->fetch_assoc()["id_product"];
                $getProdInfo = "SELECT price FROM ".$prefix."product WHERE id_product = '$idProd' ";
                $SimplePrice = $mysqli->query($getProdInfo)->fetch_assoc()['price'];
                if ($SimplePrice > 0) {
                    $priceDif = $new_price - $SimplePrice;
                    if ($priceDif != 0) {
                        $combinationProduct = "
                            UPDATE ".$prefix."product_attribute 
                            SET price = $priceDif
                            WHERE reference = '$reference' ";
                        $mysqli->query($combinationProduct);
                        if ($mysqli->affected_rows > 0) {
                            $idProdAttr = $mysqli->query($combiIdProd)->fetch_assoc()["id_product_attribute"];
                            $combinationProductShop = "
                                UPDATE ".$prefix."product_attribute_shop 
                                SET price = $priceDif
                                WHERE id_product_attribute = '$idProdAttr' ";
                            $mysqli->query($combinationProductShop);
                            $countUpdates += 1;
                        }
                    }
                }

            }
            $mysqli->close();
            header("location: NewPriceImport.php?DONE=".(int)$countUpdates);
        } else {
            $message = '<label class="text-danger">Please Select CSV File only</label>';
        }
    } else {
        $message = '<label class="text-danger">Please Select File</label>';
    }
}

if (isset($_GET["DONE"])) {
    $message = '<label class="text-success">Price Update Done!</label><br>'.'There where '.$_GET["DONE"].' prices changed.';
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Update simple and combination prices on reference</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css"/>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<br/>
<div class="container">
    <h2 align="center">Update simple and combination prices on reference<br><small class="text-muted">Comma seperated
            csv file</small></h2>
    <br/>
    <form method="post" enctype='multipart/form-data'>
        <p><label>Please Select a csv File</label>
            <input type="file" name="product_file"/></p>
        <br/>
        <input type="submit" name="upload" class="btn btn-info" value="Upload"/>
    </form>
    <center>
        <h1><?php echo $message; ?></h1>
    </center>
    <center style="padding-top: 250px;">
        <div class="card" style="width: 18rem;">
            <img src="https://inform-all.nl/img/logo.png" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Developed by</h5>
                <p class="card-text"><a href="https://inform-all.nl">Inform-All</a></p>
                <a href="https://www.paypal.me/buymecoffee" class="btn btn-primary">Donate</a>
            </div>
        </div>
    </center>
</div>
</body>
</html>
