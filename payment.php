<?php include_once 'helpers/helper.php'; ?>
<?php subview('header.php'); ?>
<link rel="stylesheet" href="assets/css/form.css">
<style>
input {
    border :0px !important;
    border-bottom: 2px solid #424242 !important;
    color :#424242 !important;
    border-radius: 0px !important;
    font-weight: bold !important;   
    margin-bottom: 10px;    
}
label {
    color : #828282 !important;
    font-size: 19px;
}  
.input-group-addon {
    background-color: transparent;
    border-left: 0;
}
.card-body {
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);  
}
@font-face {
  font-family: 'product sans';
  src: url('assets/css/Product Sans Bold.ttf');
}
h1 {
    font-size: 50px !important;
    margin-bottom: 20px;  
    font-family :'product sans' !important;
    font-weight: bolder;
}
.cc-number.identified {
    background-repeat: no-repeat;
    background-position-y: 3px;
    background-position-x: 99%;
}
.one-card > div {
    height: 150px;
    background-position: center center;
    background-repeat: no-repeat;
}
.two-card > div {
    height: 80px;
    background-position: center center;
    background-repeat: no-repeat;
    background-size: contain;
    width: 48%;
}
.two-card div.amex-cvc-preview {
    float: right;
}
body {
  background: #bdc3c7;  
  background: -webkit-linear-gradient(to right, #2c3e50, #bdc3c7);  
  background: linear-gradient(to right, #2c3e50, #bdc3c7);
}
textarea:focus, 
textarea.form-control:focus, 
input.form-control:focus, 
input[type=text]:focus, 
input[type=password]:focus, 
input[type=email]:focus, 
input[type=number]:focus, 
[type=text].form-control:focus, 
[type=password].form-control:focus, 
[type=email].form-control:focus, 
[type=tel].form-control:focus, 
[contenteditable].form-control:focus {
  box-shadow: inset 0 -1px 0 #ddd;
}
</style>

<?php 
if(isset($_SESSION['userId'])) {   
  // Xử lý lỗi nếu có
  if(isset($_GET['error'])) {
    if($_GET['error'] === 'sqlerror') {
        echo"<script>alert('Database error')</script>";
    } else if($_GET['error'] === 'noret') {
      echo"<script>alert('No return flight available')</script>";
    } else if($_GET['error'] === 'mailerr') {
      echo"<script>alert('Mail error')</script>";
    }
  }
  // Tạo mã CSRF nếu chưa có
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
?>
<main>
  <div class="container-fluid py-3">
    <div class="row">
      <div class="col-12 col-sm-8 col-md-6 col-lg-4 mx-auto">
        <h1 class="text-center text-light">HOÁ ĐƠN</h1>
        <div id="pay-invoice" class="card">
          <div class="card-body">
            <label for="fname">Hình thức</label>
            <div class="icon-container">
              <i class="fa fa-cc-visa fa-3x" style="color:navy;"></i>
              <i class="fa fa-cc-amex fa-3x" style="color:blue;"></i>
              <i class="fa fa-cc-mastercard fa-3x" style="color:red;"></i>
              <i class="fa fa-cc-discover fa-3x" style="color:orange;"></i>
               <i class="fa fa-cc-stripe fa-3x" style="color:blue;"></i>
            </div>
            <hr>
            <form action="includes/payment.inc.php" method="post" novalidate="novalidate" class="needs-validation">
  
              <!-- CSRF token -->
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

              <div class="form-group">
                <label for="cc-number" class="control-label mb-1">STK</label>
                <input id="cc-number" name="cc-number" type="tel" class="form-control cc-number identified visa" required autocomplete="off">
                <span class="invalid-feedback">Enter a valid 12 to 16 digit card number</span>
              </div>
              <div class="row">
                <div class="col-6">
                  <div class="form-group">
                    <label for="cc-exp" class="control-label mb-1">Ngày hết hạn</label>
                    <input id="cc-exp" name="cc-exp" type="tel" class="form-control cc-exp" required placeholder="MM / YY" autocomplete="cc-exp">
                    <span class="invalid-feedback">Enter the expiration date</span>
                  </div>
                </div>
                <div class="col-6 p-0">
                  <label for="x_card_code" class="control-label mb-1">CVV</label>
                  <div class="row">
                    <div class="col pr-0">
                      <input id="x_card_code" name="x_card_code" type="password" class="form-control cc-cvc" required autocomplete="off">
                    </div>
                    <div class="col pr-0">                            	
                      <span class="invalid-feedback order-last">Nhập 3 kí tự</span>
                      <div class="input-group-append">
                        <div class="input-group-text">
                          <span class="fa fa-question-circle fa-lg" data-toggle="popover" data-container="body" data-html="true" data-title="CVV" 
                            data-content="<div class='text-center one-card'>The 3 digit code on back of the card..<div class='visa-mc-cvc-preview'></div></div>"
                            data-trigger="hover"></span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <br/>
              <div class='form-row'>
                <div class='col-md-12 mb-2'>
                  <button id="payment-button" type="submit" name="pay_but" class="btn btn-lg btn-primary btn-block">
                    <i class="fa fa-lock fa-lg"></i>&nbsp;
                    <span id="payment-button-amount">Trả </span>
                    <span id="payment-button-sending" style="display:none;">Đang gửi...</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<?php subview('footer.php'); ?> 

<script>
$(document).ready(function(){
  $('.input-group input').focus(function(){
    me = $(this);
    $("label[for='"+me.attr('id')+"']").addClass("animate-label");
  });
  $('.input-group input').blur(function(){
    me = $(this);
    if ( me.val() == ""){
      $("label[for='"+me.attr('id')+"']").removeClass("animate-label");
    }
  });
});

$(function () {
  $('[data-toggle="popover"]').popover()
})

$("#payment-button").click(function(e) {
  var form = $(this).parents('form');
  
  var cvv = $('#x_card_code').val();
  var regCVV = /^[0-9]{3,4}$/;
  var CardNo = $('#cc-number').val();
  var regCardNo = /^[0-9]{12,16}$/;
  var date = $('#cc-exp').val().split('/'); // lấy tháng và năm từ input
  var regMonth = /^(0[1-9]|1[0-2])$/; // Kiểm tra tháng hợp lệ (01-12)
  var regYear = /^[0-9]{2}$/; // Kiểm tra năm hợp lệ (2 chữ số)

  // Kiểm tra tính hợp lệ của số thẻ và mã CVV
  if (form[0].checkValidity() === false) {
    e.preventDefault();
    e.stopPropagation();
  }
  else {
    if (!regCardNo.test(CardNo)) {
      $("#cc-number").addClass('required');
      alert("Vui lòng nhập số thẻ hợp lệ (12-16 chữ số).");
      return false;
    }
    else if (!regCVV.test(cvv)) {
      $("#x_card_code").addClass('required');
      alert("Vui lòng nhập mã CVV hợp lệ (3-4 chữ số).");
      return false;
    }
    else if (!regMonth.test(date[0])) {
      $("#cc-exp").addClass('required');
      alert("Vui lòng nhập tháng hết hạn hợp lệ (01-12).");
      return false;
    }
    else if (!regYear.test(date[1])) {
      $("#cc-exp").addClass('required');
      alert("Vui lòng nhập năm hết hạn hợp lệ (2 chữ số).");
      return false;
    }

    // Kiểm tra nếu ngày hết hạn nhỏ hơn ngày hiện tại
    var currentDate = new Date();
    var currentMonth = currentDate.getMonth() + 1; // tháng hiện tại (1-12)
    var currentYear = currentDate.getFullYear(); // năm hiện tại (yyyy)

    var expMonth = parseInt(date[0]);
    var expYear = parseInt("20" + date[1]); // chuyển "YY" thành "YYYY"

    // Kiểm tra nếu ngày hết hạn nhỏ hơn ngày hiện tại
    if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
      $("#cc-exp").addClass('required');
      alert("Ngày hết hạn không hợp lệ. Vui lòng nhập ngày hết hạn trong tương lai.");
      return false;
    }

    // Nếu tất cả hợp lệ, gửi form
    form.submit(); 
  }

  form.addClass('was-validated');
});
</script>
<?php 
} else {
    header("Location: login.php");
    exit();
}  
?>
