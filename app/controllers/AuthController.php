<?php
declare(strict_types=1);
namespace HotSpot\Controllers;
use HotSpot\Core\Controller;
use HotSpot\Models\User;
use PDOException;
use Throwable;
final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (current_user()) { redirect_to('home'); return; }
        $this->render('auth/login',['title'=>'Đăng nhập','next'=>safe_next($_GET['next']??'home'),'email'=>'']);
    }
    public function login(): void
    {
        $email=strtolower(trim(input_text($_POST,'email')));
        $password=input_text($_POST,'password');
        $next=safe_next($_POST['next']??'home');
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254 || $password==='' || strlen($password)>72 || strpos($password,"\0")!==false) {
            flash('danger','Email hoặc mật khẩu không chính xác.');
            redirect_to('auth/login',['next'=>$next]); return;
        }
        try {
            $user=User::findByEmail($email);
            if ($user && password_verify($password,$user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user']=['user_id'=>(int)$user['user_id'],'role_code'=>$user['role_code'],
                    'full_name'=>$user['full_name'],'email'=>$user['email']];
                unset($_SESSION['_csrf']);
                flash('success','Đăng nhập thành công.');
                redirect_to($next); return;
            }
            flash('danger','Email hoặc mật khẩu không chính xác.');
        } catch (Throwable $e) {
            flash('danger','Chưa thể đăng nhập. Vui lòng thử lại sau.');
        }
        redirect_to('auth/login',['next'=>$next]);
    }
    public function registerForm(): void
    {
        if (current_user()) { redirect_to('home'); return; }
        $this->render('auth/register',['title'=>'Đăng ký','errors'=>[],'form'=>[]]);
    }
    public function register(): void
    {
        $name=trim(input_text($_POST,'full_name'));
        $email=strtolower(trim(input_text($_POST,'email')));
        $password=input_text($_POST,'password');
        $confirm=input_text($_POST,'password_confirmation');
        $errors=[];
        if (!preg_match('/^(?=.*[^\s]).{1,120}$/us',$name)) $errors['full_name']='Họ tên cần từ 1 đến 120 ký tự.';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254) $errors['email']='Email không hợp lệ.';
        if (strlen($password)<8 || strlen($password)>72 || strpos($password,"\0")!==false) $errors['password']='Mật khẩu cần từ 8 đến 72 byte (ký tự có dấu có thể chiếm nhiều byte).';
        if ($password!==$confirm) $errors['password_confirmation']='Xác nhận mật khẩu không khớp.';
        if ($errors) { $this->registrationErrors($errors,$name,$email,422); return; }
        try {
            User::create($name,$email,password_hash($password,PASSWORD_BCRYPT));
            flash('success','Tạo tài khoản thành công. Mời đăng nhập.');
            redirect_to('auth/login'); return;
        } catch (PDOException $e) {
            if ((int)($e->errorInfo[1]??0)===1062) {
                $this->registrationErrors(['email'=>'Email này đã tồn tại.'],$name,$email,409); return;
            }
        } catch (Throwable $e) { /* Display a basic error below. */ }
        $this->registrationErrors(['general'=>'Chưa thể đăng ký. Vui lòng thử lại sau.'],$name,$email,503);
    }
    private function registrationErrors(array $errors,string $name,string $email,int $status): void
    {
        $this->render('auth/register',['title'=>'Đăng ký','errors'=>$errors,'form'=>compact('name','email')],$status);
    }
    public function logout(): void
    {
        $_SESSION=[];
        session_regenerate_id(true);
        flash('success','Đã đăng xuất.');
        redirect_to('home');
    }
}
