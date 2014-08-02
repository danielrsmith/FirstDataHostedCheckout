<?php
require_once 'FirstDataHostedCheckout.class.php';
require_once 'config.php';


if(!isset($config))
{
    die('A configuration needs to be set.');
}

$cart = new FirstDataHostedCheckout($config);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $input = $_POST;
}
else
{
    $input = $_GET;
}


if(isset($input['amount']))
{
    $amount = floatval($input['amount']);
}
else
{
    die('Payment amount required.');
}

if(isset($input['item']))
{
    $item = trim($input['item']);
}
else
{
    $item = 'Generic Item';
}


if(isset($input['recurrence']) && isset($input['start_date']) && isset($input['end_date']))
{
    $recurrence = trim($input['recurrence']);
    $start = trim($input['start_date']);
    $end = trim($input['end_date']);
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