<?php
require_once 'FirstDataHostedCheckout.class.php';
require_once 'config.php';


if(!isset($config))
{
    die('A configuration needs to be set.');
}

$cart = new FirstDataHostedCheckout($config);

if(isset($_GET['amount']))
{
    $amount = floatval($_GET['amount']);
}
else
{
    die('Payment amount required.');
}

if(isset($_GET['item']))
{
    $item = trim($_GET['item']);
}
else
{
    $item = 'Generic Item';
}


if(isset($_GET['recurrence']) && isset($_GET['start_date']) && isset($_GET['end_date']))
{
    $recurrence = trim($_GET['recurrence']);
    $start = trim($_GET['start_date']);
    $end = trim($_GET['end_date']);
}
else
{
    $recurrence = 'none';
    $start = null;
    $end = null;
}
try
{
    $form = $cart->createHostedCheckout($amount, $item, $item, $recurrence, $start, $end)->generateHostedCheckout();
}
catch(Exception $e)
{
    die($e->getMessage());
}

?>
<html>
<head>
    <title>Shopping Cart Redirect</title>
</head>
<?php echo $form; ?>
<script type="application/javascript">
    document.getElementById('paymentForm').submit();
</script>
</html>