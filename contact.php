<?php require_once('header.php'); ?>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_page WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $contact_title = $row['contact_title'];
    $contact_banner = $row['contact_banner'];
}
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $contact_map_iframe = $row['contact_map_iframe'];
    $contact_email = $row['contact_email'];
    $contact_phone = $row['contact_phone'];
    $contact_address = $row['contact_address'];
}
?>

<div class="page-banner" style="background-image: url(assets/uploads/<?php echo $contact_banner; ?>);">
    <div class="inner">
        <h1><?php echo $contact_title; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">            
            <div class="col-md-12">
                <h3>Mẫu liên hệ</h3>
                <div class="row cform">
                    <div class="col-md-8">
                        <div class="well well-sm">
                            
                            <?php
// After form submit checking everything for email sending
if(isset($_POST['form_contact']))
{
    $error_message = '';
    $success_message = '';
    $statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);                           
    foreach ($result as $row) 
    {
        $receive_email = $row['receive_email'];
        $receive_email_subject = $row['receive_email_subject'];
        $receive_email_thank_you_message = $row['receive_email_thank_you_message'];
    }

    $valid = 1;

    if(empty($_POST['visitor_name']))
    {
        $valid = 0;
        $error_message .= 'Vui lòng nhập tên của bạn.\n';
    }

    if(empty($_POST['visitor_phone']))
    {
        $valid = 0;
        $error_message .= 'Vui lòng nhập số điện thoại của bạn.\n';
    }


    if(empty($_POST['visitor_email']))
    {
        $valid = 0;
        $error_message .= 'Vui lòng nhập địa chỉ email của bạn.\n';
    }
    else
    {
        // Email validation check
        if(!filter_var($_POST['visitor_email'], FILTER_VALIDATE_EMAIL))
        {
            $valid = 0;
            $error_message .= 'Vui lòng nhập địa chỉ email hợp lệ.\n';
        }
    }

    if(empty($_POST['visitor_message']))
    {
        $valid = 0;
        $error_message .= 'Vui lòng nhập tin nhắn của bạn.\n';
    }

    if($valid == 1)
    {
        
        $visitor_name = strip_tags($_POST['visitor_name']);
        $visitor_email = strip_tags($_POST['visitor_email']);
        $visitor_phone = strip_tags($_POST['visitor_phone']);
        $visitor_message = strip_tags($_POST['visitor_message']);

        // sending email
        $to_admin = $receive_email;
        $subject = $receive_email_subject;
        $message = '
<html><body>
<table>
<tr>
<td>Tên</td>
<td>'.$visitor_name.'</td>
</tr>
<tr>
<td>Email</td>
<td>'.$visitor_email.'</td>
</tr>
<tr>
<td>Số điện thoại</td>
<td>'.$visitor_phone.'</td>
</tr>
<tr>
<td>Bình luận</td>
<td>'.nl2br($visitor_message).'</td>
</tr>
</table>
</body></html>
';
       
        
        try {
		    
	    $mail->setFrom($visitor_email, $visitor_name);
	    $mail->addAddress($to_admin);
	    $mail->addReplyTo($visitor_email, $visitor_name);
	    
	    $mail->isHTML(true);
	    $mail->Subject = $subject;

	    $mail->Body = $message;
	    $mail->send();

	    $success_message = $receive_email_thank_you_message;
	} catch (Exception $e) {
	    echo 'Không thể gửi thư.';
	    echo 'Mailer Lỗi: ' . $mail->ErrorInfo;
	}
        
        

    }
}
?>
                
                <?php
                if($error_message != '') {
                    echo "<script>alert('".$error_message."')</script>";
                }
                if($success_message != '') {
                    echo "<script>alert('".$success_message."')</script>";
                }
                ?>


                            <form action="" method="post">
                            <?php $csrf->echoInputField(); ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Tên</label>
                                        <input type="text" class="form-control" name="visitor_name" placeholder="Nhập tên">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Địa chỉ Email</label>
                                        <input type="email" class="form-control" name="visitor_email" placeholder="Nhập địa chỉ Email">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Số điện thoại</label>
                                        <input type="text" class="form-control" name="visitor_phone" placeholder="Nhập số điện thoại">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Tin nhắn</label>
                                        <textarea name="visitor_message" class="form-control" rows="9" cols="25" placeholder="Nhập tin nhắn"></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <input type="submit" value="Gửi tin nhắn" class="btn btn-primary pull-right" name="form_contact">
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <legend><span class="glyphicon glyphicon-globe"></span> Văn phòng của chúng tôi</legend>
                        <address>
                            <?php echo nl2br($contact_address); ?>
                        </address>
                        <address>
                            <strong>Điện thoại:</strong><br>
                            <span><?php echo $contact_phone; ?></span>
                        </address>
                        <address>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo $contact_email; ?>"><span><?php echo $contact_email; ?></span></a>
                        </address>
                    </div>
                </div>

                <h3>Tìm chúng tôi trên bản đồ</h3>
                <?php echo $contact_map_iframe; ?>
                
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>