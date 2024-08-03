<?php

use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\check;
use App\Http\Controllers\Logout;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use \App\Http\Controllers\RestPasswordController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PagesController::class, 'index'])->name('index');

Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
   Route::get('/dashboard', [PagesController::class, 'patientDashboard']) ->name('dashboard');
});



//---------------------------------------/
Route::post("/login/custom",[LoginController::class,"login"]);
Route::get("/Logout",[Logout::class,"logout"]);
Route::Post("/Register/Create",[RegisterController::class,"create"]);
Route::post("/password/update",[RestPasswordController::class,"update"]);
Route::view("/Home","index")->name("index");

Route::group(['middleware' => ['isDoctor']], function() {
    Route::get('/doctor', [DoctorController::class, 'index'])->name('doctor-dashboard');
    Route::post('/doctor', [DoctorController::class, 'store'])->name('doctor-dashboard');
    Route::get('/search/patient', [DoctorController::class, 'findPatient'])->name('doctor-dashboard');

    Route::get('/doctor/find-patient', function () {
        return view('pages.doctor-find-patient');
    });

    Route::get('/next-patient/{emptyTable}', [DoctorController::class, 'nextPatient']);
    Route::get('/doctor/show-appointments/{day}/{month}', [DoctorController::class, 'showAvailableAppointments']);
    Route::get('/doctor/manage-appointments', [DoctorController::class, 'manageAppointments'])->name('manage_appointments');
});

Route::group(['middleware' => ['isPatient']], function() {

    Route::get('/patient/history', function () {
        return view('patient.my-history');
    });


    Route::get("/patient",[PatientController::class,"index"])->middleware('Patient.fourm');;
    //Route::get("/contactus",[PatientController::class,"showContactUs"]);

    Route::get("/makeappointment",[PatientController::class,"showAppointment"])->middleware('Patient.fourm');
//    Route::get("/deleteaccount",[PatientController::class,"showDeleteAccount"])->middleware('Patient.fourm');;
    Route::get("/delete/account",[PatientController::class,"DeleteAccount"]);
   //Route::get("/resetpasswordpatient",[PatientController::class,"showResetPassword"]);
    //Route::get("/patient/Reserve",[PatientController::class,"create"]);
    Route::get("/patient/fourm/store",[PatientController::class,"store"]);
    Route::get("/patient/fourm/edit",[PatientController::class,"edit"]);
    Route::get("/patient/fourm/update",[PatientController::class,"update"]);
    Route::get("/history",[PatientController::class,"showHistory"])->middleware('Patient.fourm');
    Route::get("/history/details/{id}",[PatientController::class,"showHistoryDetails"])->middleware('Patient.fourm');
    Route::get('/pdf/generate/{id}', [PatientController::class, 'generatePDF']);


    Route::get('/patient/show-appointments/{day}/{month}', [PatientController::class, 'showAvailableAppointments']);
    Route::get('/delete-appointment/{day}/{month}/{from}', [PatientController::class, 'removeAppointment']);
    Route::get('/create-appointment/{day}/{month}/{from}', [PatientController::class, 'createAppointment']);
    Route::get('/show-my-appointments', [PatientController::class, 'showMyAppointments']);

    Route::view('/patient-fourm','patient.patient_fourm');
});

Route::group(['middleware' => ['isNurse']], function() {
    Route::get('/nurse', [NurseController::class, 'index'])->name('nurse-dashboard');

    Route::get('/nurse/reservations', [NurseController::class, 'reservations']);

    Route::get('/nurse/reserve', [NurseController::class, 'createReserve'])->name('nurse-reserve');

    Route::get('/nurse/working-hours', [NurseController::class, 'createWorkHourException'])->name('working-hours');

    Route::get('/nurse/reserve/store', [NurseController::class,"store"]);

    Route::get('/update/schedule', [NurseController::class,"update"]);

    //////---------------------------------------------------------
    Route::get("/patient/attend/{id}",[NurseController::class,"CheckAttend"]);
    Route::get("/patient/notattend/{id}",[NurseController::class,"CheckNotAttend"]);
    //-------------------------------------------------------------
    Route::get('/nurse/show-appointments/{day}/{month}', [NurseController::class, 'showAvailableAppointments']);
    Route::get('/notification', [NurseController::class, 'notification']);
});


Route::get("/dashboard/user/",[check::class,"checkRole"]);
Route::view('/noacess','noacess');

Route::view("password/request","auth.forgot-password");
