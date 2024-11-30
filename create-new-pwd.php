<?php include_once 'helpers/helper.php'; ?>
<?php subview('header.php'); ?>

<link rel="stylesheet" href="assets/css/login.css">
<style>
@font-face {
  font-family: 'product sans';
  src: url('assets/css/Product Sans Bold.ttf');
}
h1{
   font-family :'product sans' !important;
	font-size:48px !important;
	margin-top:20px;
	text-align:center;
}
body {
    background: -webkit-linear-gradient(left, #3931af, #00c6ff);
}
.login-form {
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);  
    border-radius: 0px;
}
</style>
<?php
if(isset($_GET['err']) || isset($_GET['pwd'])) {
    if($_GET['err'] === 'pwdnotmatch') {
        echo '<script>alert("Mật khẩu k trùng");</script>';
    } else if($_GET['err'] === 'sqlerr') {
        echo '<script>alert("Có lỗi");</script>';        
    } else if($_GET['pwd'] === 'updated') {
        echo '<script>alert("Mật khẩu đã được lưu");</script>';        
    }                      
    exit();
} 
?>
<div class="flex-container">    
<div class="login-form mt-4" style="height: 300px;">
    <h1 class="text-primary mb-3 text-center">Đổi mật khẩu</h1>
    <?php 
    $selector = $_GET['selector'];
    $validator = $_GET['validator'];    
    if(empty($selector) || empty($validator)){
        echo '<script>alert("Could not validate your request")</script>'; 
    } else {
        if(ctype_xdigit($selector) !== false && ctype_xdigit($validator) !== false){
            ?>
            <form method="POST" action="includes/reset-password.inc.php">
                <input type="hidden" name="selector" value="<?php echo $selector ?>">
                <input type="hidden" name="validator" value="<?php echo $validator ?>">
                <div class="flex-container">             
                    <div>
                        <i class="fa fa-lock text-primary"></i>
                    </div>
                    <div>
                        <input type="password" name="password" class="form-input" 
                            placeholder="Nhập mật khẩu" 
                            required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                            title="Phải chứa ít nhất một số và một chữ hoa và chữ thường, 
                            và ít nhất 8 ký tự trở lên">                       
                    </div>
                </div>
                <div class="flex-container">
                    <div>
                        <i class="fa fa-lock text-primary"></i>
                    </div>
                    <div>
                        <input type="password" name="password_repeat" class="form-input" 
                            placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>                
                <div class="submit">
                <button name="new-pwd-submit" type="submit" class="button">
                    Lưu</button>                                       
                </div>
            </form> 
            <?php
            }            
    }
    ?>                             
</div>
</div>
<?php subview('footer.php'); ?> 
