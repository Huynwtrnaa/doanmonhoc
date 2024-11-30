<?php
session_start();

// Hàm để lấy số ghế mới
function getNewSeat($last_seat) {
    // Cách thức lấy số ghế mới, ví dụ đơn giản:
    // Nếu ghế là A01, B02 thì bạn cần tạo logic cho việc chuyển số ghế
    $new_seat = $last_seat;  // Sửa lại logic nếu cần
    return $new_seat;
}

// Cập nhật số ghế còn lại trong cơ sở dữ liệu
function updateFlightSeats($conn, $flight_id, $class, $new_seat) {
    $sql = "";
    if ($class === 'B') {
        $sql = "UPDATE Flight SET last_bus_seat=? , bus_seats = bus_seats - 1 WHERE flight_id=?";
    } else {
        $sql = "UPDATE Flight SET last_seat=? , Seats = Seats - 1 WHERE flight_id=?";
    }
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'si', $new_seat, $flight_id);
    return mysqli_stmt_execute($stmt);
}

// Kiểm tra thẻ tín dụng trùng lặp
function checkCardExists($conn, $card_no) {
    $sql_check_card = "SELECT card_no FROM payment WHERE card_no = ?";
    $stmt_check_card = mysqli_prepare($conn, $sql_check_card);
    mysqli_stmt_bind_param($stmt_check_card, 's', $card_no);
    mysqli_stmt_execute($stmt_check_card);
    mysqli_stmt_store_result($stmt_check_card);
    return mysqli_stmt_num_rows($stmt_check_card) > 0;
}

// Chèn thông tin thanh toán và vé
function processPaymentAndTickets($conn, $user_id, $flight_id, $card_no, $price, $class, $passengers, $pass_id, $type, $ret_date, $is_round_trip) {
    // Thực hiện thanh toán và lưu vào cơ sở dữ liệu
    $sql = 'INSERT INTO PAYMENT (user_id, expire_date, amount, flight_id, card_no) VALUES (?,?,?,?,?)';
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header('Location: ../payment.php?error=sqlerror');
        exit();
    } else {
        mysqli_stmt_bind_param($stmt, 'isiis', $user_id, date('m/y'), $price, $flight_id, $card_no);
        mysqli_stmt_execute($stmt);
    }

    // Phân bổ ghế và cập nhật số ghế còn lại
    $flag = false;
    for ($i = $pass_id; $i <= $passengers + $pass_id; $i++) {
        $sql = 'SELECT * FROM Flight WHERE flight_id=?';
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header('Location: ../payment.php?error=sqlerror');
            exit();
        } else {
            mysqli_stmt_bind_param($stmt, 'i', $flight_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $source = $row['source'];
                $dest = $row['Destination'];
                $new_seat = getNewSeat($row['last_seat']);  // Gọi hàm getNewSeat
                if (!updateFlightSeats($conn, $flight_id, $class, $new_seat)) {
                    header('Location: ../payment.php?error=sqlerror');
                    exit();
                }

                // Kiểm tra xem passenger_id có tồn tại trong bảng passenger_profile
                $sql_check_passenger = "SELECT passenger_id FROM passenger_profile WHERE passenger_id = ?";
                $stmt_check_passenger = mysqli_prepare($conn, $sql_check_passenger);
                mysqli_stmt_bind_param($stmt_check_passenger, 'i', $i);
                mysqli_stmt_execute($stmt_check_passenger);
                mysqli_stmt_store_result($stmt_check_passenger);

                if (mysqli_stmt_num_rows($stmt_check_passenger) > 0) {
                    // Chèn vé vào cơ sở dữ liệu
                    $stmt = mysqli_stmt_init($conn);
                    $sql = 'INSERT INTO Ticket (passenger_id, flight_id, seat_no, cost, class, user_id) VALUES (?,?,?,?,?,?)';
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        header('Location: ../payment.php?error=sqlerror');
                        exit();
                    } else {
                        mysqli_stmt_bind_param($stmt, 'iisisi', $i, $flight_id, $new_seat, $price, $class, $user_id);
                        mysqli_stmt_execute($stmt);
                        $flag = true;
                    }
                } else {
                    header('Location: ../payment.php?error=invalid_passenger');
                    exit();
                }
            } else {
                header('Location: ../payment.php?error=sqlerror');
                exit();
            }
        }
    }

    // Nếu là vé khứ hồi, thực hiện các bước tương tự cho chuyến bay ngược
    if ($is_round_trip && $flag) {
        $flag = false;
        for ($i = $pass_id; $i <= $passengers + $pass_id; $i++) {
            $sql = 'SELECT * FROM Flight WHERE source=? AND Destination=? AND DATE(arrivale)=?';
            $stmt = mysqli_stmt_init($conn);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                header('Location: ../payment.php?error=sqlerror');
                exit();
            } else {
                mysqli_stmt_bind_param($stmt, 'sss', $dest, $source, $ret_date);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($result)) {
                    $flight_id = $row['flight_id'];
                    $new_seat = getNewSeat($row['last_seat']);  // Gọi hàm getNewSeat
                    if (!updateFlightSeats($conn, $flight_id, $class, $new_seat)) {
                        header('Location: ../payment.php?error=sqlerror');
                        exit();
                    }

                    // Chèn vé cho chuyến bay ngược
                    $stmt = mysqli_stmt_init($conn);
                    $sql = 'INSERT INTO Ticket (passenger_id, flight_id, seat_no, cost, class, user_id) VALUES (?,?,?,?,?,?)';
                    if (!mysqli_stmt_prepare($stmt, $sql)) {
                        header('Location: ../payment.php?error=sqlerror');
                        exit();
                    } else {
                        mysqli_stmt_bind_param($stmt, 'iisisi', $i, $flight_id, $new_seat, $price, $class, $user_id);
                        mysqli_stmt_execute($stmt);
                        $flag = true;
                    }
                } else {
                    header('Location: ../my_flights.php');
                    exit();
                }
            }
        }
    }

    return $flag;
}

if (isset($_POST['pay_but']) && isset($_SESSION['userId'])) {
    require '../helpers/init_conn_db.php';

    $flight_id = $_SESSION['flight_id'];
    $price = $_SESSION['price'];
    $passengers = $_SESSION['passengers'];
    $pass_id = $_SESSION['pass_id'];
    $type = $_SESSION['type'];
    $class = $_SESSION['class'];
    $ret_date = $_SESSION['ret_date'];
    $card_no = $_POST['cc-number'];
    $expiry = $_POST['cc-exp'];

    if (checkCardExists($conn, $card_no)) {
        header('Location: ../payment.php?error=card_exists');
        exit();
    }

    $payment_flag = processPaymentAndTickets($conn, $_SESSION['userId'], $flight_id, $card_no, $price, $class, $passengers, $pass_id, $type, $ret_date, $_SESSION['is_round_trip']);
    if ($payment_flag) {
        header('Location: ../my_flights.php');
    } else {
        header('Location: ../payment.php?error=payment_failed');
    }
}
?>
