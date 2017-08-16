<?php session_start(); ?>
<html>
<head><title>Buy Products</title></head>
<body>
<table border="1" width="1200" height="750">
<tr width="1000" height="2" >
<td colspan="2">
<form action="buy.php" method="GET">
<fieldset><legend>Find products:</legend>
<label>Category: <select name="category">
<?php 
error_reporting(E_ALL);
ini_set('display_errors','On');
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true' );
$xml = new SimpleXMLElement($xmlstr);
foreach ($xml->category->categories ->category  as $c){
	  print "<optgroup label=".$c->name.">";
		foreach($c->categories ->category as $p){
		print "<option value=".$p['id'].">".$p->name."</option>";
		}
		print "</optgroup>";
    } ?>
</select>
</label>
<label>Search keywords: <input type="text" name="search"/><label>
<input type="submit" value="Search"/>
</fieldset>
</form>
</td>
</tr>
<tr>
<td width="500">
<?php
if(isset($_GET['search']))
{

$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610'.'&categoryId='.$_GET['category'].'&keyword='.$_GET['search']);
$xml = new SimpleXMLElement($xmlstr);
$_SESSION['last_search'] = $xmlstr;
?>
<table border="1">
<?php
	foreach ($xml->categories ->category  as $c){
		foreach($c->items->product as $p){?>
		<tr>
			<td>
				<a href=<?php print 'buy.php?buy='. $p['id'] ?> >
					<img src=<?php print $p->images->image->sourceURL;?> />
				</a>
			</td>
			<td><?php print $p->name;?></td>
			<td><?php print '$' . $p->minPrice;?></td>
			<td><?php print $p->fullDescription;?></td>
		</tr>
	<?php }
	}	
?>
</table>
<?php } ?>
</td>
<td width="500">
<?php
if(isset($_GET['buy']))
{
	buy();			
}

function buy(){
	if(!isset($_SESSION['shopping_basket'])){
		$_SESSION['shopping_basket'] = [];
	}
		
	foreach ($_SESSION['shopping_basket'] as $sb){ 
		if($sb['product']['product_id'] == $_GET['buy'])
		{
			return;
		}
	}
	$xml = new SimpleXMLElement($_SESSION['last_search']);
	foreach ($xml->categories ->category  as $c){
		foreach($c->items->product as $p){
			if($p['id'] == $_GET['buy']){
				$product = array(
					"product"=>array(
					"product_id" => (string)$p['id'],
					"product_name" => (string)$p->name,
					"product_minprice" => (float)$p->minPrice,
					"product_image" => (string)$p->images->image->sourceURL,
					"product_productOffersURL" => (string)$p->productOffersURL
					)
				);
				
				array_push($_SESSION['shopping_basket'], $product);					
				break;
			}			
		}
	}
}

if(isset($_GET['clear']))
{
	emptyBasket();
	
}

function emptyBasket()
{
unset($_SESSION['shopping_basket'])	;	
}

if(isset($_GET['delete'])){
	deleteItem();
}

function deleteItem()
{
	foreach ($_SESSION['shopping_basket'] as $key => $sb)
	{ 
		if($sb['product']['product_id'] == $_GET['delete'])
		{
			unset($_SESSION['shopping_basket'][$key]);
			break;
		}
	}
}

$total = 0;
if(isset($_SESSION['shopping_basket']) && !empty($_SESSION['shopping_basket'])) { ?>
	<p>Shopping Basket:</p>
	<table border=1>
	<?php 
	foreach ($_SESSION['shopping_basket'] as $sb){ 
	$total = $total + $sb['product']['product_minprice'];
	?>
		<tr>
			<td>
				<a href=<?php print $sb['product']['product_productOffersURL']; ?> >
					<img src=<?php print $sb['product']['product_image']; ?> />
				</a>
			</td>
			<td><?php print $sb['product']['product_name']; ?></td>
			<td><?php print $sb['product']['product_minprice']; ?></td>
			<td><a href=<?php print 'buy.php?delete='. $sb['product']['product_id']; ?>>Delete</a></td>
		</tr>
	<?php } ?>
	</table>
<p/>Total: <?php print '$'.$total?><p/>
<form action="buy.php" method="GET">
<input type="hidden" name="clear" value="1"/>
<input type="submit" value="Empty Basket"/>
</form>
<?php } ?>

</td>
</tr>
</body>
</html>