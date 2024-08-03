<?php

namespace App\Http\Controllers;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientHistory;
use App\Models\Patientturn;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Exception_Days_Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Illness;
use PDF;
use function GuzzleHttp\Psr7\str;

class PatientController extends Controller
{


    public static function index()
    {
        $user = User::find(auth()->user()->id);
        $Ilness = Illness::Where("user_id",$user->id)->orderBy('created_at','DESC')->first();
        $reservation = Reservation::whereBetween('reservation At',[Carbon::today()->toDateTime(),Carbon::today()->addHours(22)->addMonths(4)->toDateTime()])->where('user_id',$user->id)->orderBy('reservation At','asc')->first();
//        return view('patient.history',["Ilness"=>$Ilness]);

        return view('patient.home',["user"=>$user,"illness"=>$Ilness,"reservation"=>$reservation]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        //return view("patient.makeappointment");

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        $request->validate([
            "national-id"=>"required",
            "address"=>"required",
            "age"=>"required",
            "gender"=>"required",
        ]);
        $user =User::find(auth()->user()->id);
        $patient = new Patient();
        $patient["national-id"]= $request->input("national-id");
        $patient["address"]= $request->input("address");
        $patient["age"]= $request->input("age");
        $patient["gender"]= $request->input("gender");
        $patient["user_id"]= $user->id;
        $patient->save();

        $user["PatientForum"] = 1;
        $user->save();

        $userhistory = new PatientHistory();
        $userhistory["user_id"]=$user->id;
        $userhistory->save();

        return redirect('/patient');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }


    public function edit()
    {   $user = User::find(auth()->user()->id);
        return view("patient.edit-profile",["user"=>$user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $request->validate([
            "name"=>"required",
            "address"=>"required",
            "age"=>"required",
            "email"=>"required",
            "phone"=>"required",
            "national-id"=>"required",
            "gender"=>"required",
            "password"=>"required",
            "password_confirmation"=>"required | same:password",
        ]);
        $user = User::find(auth()->user()->id);
        $patient = Patient::firstWhere("user_id",$user->id);

        $user["name"] = $request->input("name");
        $user["email"] = $request->input("email");
        if($request->input("email") !== "example@gmail.com") {
            $user["phoneNumber"] = $request->input("phone");
            $user["password"] = Hash::make($request->input("password"));
        }else{
            return redirect()->back()->with('messageError','Password Didn\'t Changed Please Confirm Your Email');
        }
        Auth::login($user);
        $user->update();


        $patient["national-id"] = $request->input("national-id");
        $patient["address"] = $request->input("address");
        $patient["age"] = $request->input("age");
        $patient["gender"] = $request->input("gender");
        $patient->update();

        return redirect("/patient");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function DeleteAccount(){
        $user = User::find(auth()->user()->id);
        $user->delete();
        return redirect('/');
    }

    public function showContactUs()
    {
        return view('patient.contactus');
    }
    public function showHistory() //
    {
        $user = User::find(auth()->user()->id);
        $illness =Illness::where("user_id",$user["id"])->orderBy('created_at','DESC')->get();
        return view('patient.history',["illness"=>$illness]);
    }
    public function showHistoryDetails($id) //
    {
        $illness = Illness::find($id);
        return view("patient.my-history",["illness"=>$illness]);
    }
    public function showAppointment()
    {
        return view('patient.makeappointment');
    }
    public function showDeleteAccount()
    {
        return view('patient.deleteaccount');
    }
    public function showResetPassword()
    {
        return view('patient.resetpasswordpatient');
    }

    public function generatePDF($id){
//        set_time_limit(300);
       $illness = Illness::find($id);
       $doctor = User::firstWhere("Role",1);
        $pdf = PDF::loadView('patient.pdf',["illness"=>$illness,"doctor"=>$doctor]);
        return $pdf->download('Prescription.pdf');
    }



    public function showAvailableAppointments($day, $month) {
        $dateTimeFrom =  Carbon::today(); //year / month/ day
        $dateTimeFrom->month = $month;
        $dateTimeFrom->day = $day;
        $dateTimeFrom->addHours(8);
        $dateTimeTo = clone $dateTimeFrom;
        $dateTimeTo->addHours(14);


        $datenow = Carbon::now();
        $start = clone Carbon::today();
        $start->addHours(8);
        $today = clone $start;
        if($start->isPast()) {
            for ($date = clone $start; $date->diffInMinutes($datenow) >= "30"; $date->addMinutes(30)) {
                $check = Reservation::firstWhere("reservation At", $date->toDateTimeString());
                if ($check === null) {
                    $res = new Reservation();
                    $res["reservation At"] = $date;
                    $res["user_id"] = 0;
                    $res["Reserved_by_Doctor"] = 1;
                    $res->save();
                }
                $today = $date;
            }
            $end = clone $today;
            $check2 = Reservation::firstWhere("reservation At", $end->toDateTimeString());
            if($check2 === null ){
                $res = new Reservation();
                $res["reservation At"] = $end;
                $res["user_id"] = 0;
                $res["Reserved_by_Doctor"] = 1;
                $res->save();
            }
        }
        $reservedobj = Reservation::whereBetween('reservation At',[$dateTimeFrom->toDateTime(),$dateTimeTo->toDateTime()])->get();

        $reserved = array();
        $isreserved = true;

        foreach ($reservedobj as $res){
            $first = Carbon::parse($res["reservation At"])->format('Hi');
            $first = (int)$first;
            array_push($reserved,$first);

            if($res["user_id"] === auth()->user()->id){
                $isreserved= false;
            }
        }

        array_push($reserved,$isreserved);
        echo json_encode($reserved);    // Echo Available Appointments Fro Day/Month
    }

    public function removeAppointment($day, $month, $from) {
        // Delete Appointment From DB
        $user = User::find(auth()->user()->id);
        $dateTimeFrom =  Carbon::today(); // min //hour// day // month // year
        $hourMin = $from;
        $hour = $from /100 ;
        $min = $from%100;

        $dateTimeFrom->addMinutes($min);
        $dateTimeFrom->addHours($hour);
        $dateTimeFrom->day = $day;
        $dateTimeFrom->month = $month;

        $reserveCheck= Reservation::where("user_id",$user["id"])->where("reservation At", $dateTimeFrom->toDateTime())->get();
        if(count($reserveCheck)!=0)
        {
            $reserveCheck[0]->delete();
        }
    }

    public function createAppointment($day, $month, $from) {
        // Add Appointment To DB
        // 930
        $user = User::find(auth()->user()->id);
        $dateTimeFrom =  Carbon::today(); // min //hour// day // month // year
        $hourMin = $from;
        $hour = $from /100 ;
        $min = $from%100;

        $dateTimeFrom->day = $day;
        $dateTimeFrom->month = $month;
        $dateTimeFrom1 = clone $dateTimeFrom;
        $dateTimeFrom->addMinutes($min);
        $dateTimeFrom->addHours($hour);


//        echo $dateTimeFrom ;
       $isreserved = Reservation::where("user_id",$user["id"])->whereBetween('reservation At',[$dateTimeFrom1->toDateTime(),$dateTimeFrom1->addHours(22)->toDateTime()])->get();

        if(count($isreserved)===0) {
            $reserve = new Reservation();
            $reserve["reservation At"] = $dateTimeFrom;
            $reserve["user_id"] = $user->id;
            $reserve->save();
        }
    }

    public function showMyAppointments() {
        $user = User::find(auth()->user()->id);
        $reserve = Reservation::where("user_id",$user["id"])->whereBetween('reservation At',[Carbon::today()->toDateTime(),Carbon::today()->addHours(22)->addMonths(4)->toDateTime()])->orderBy("reservation At","asc")->get();
        $myAppointments = array();

        foreach ($reserve as $res){
            $date = Carbon::parse($res["reservation At"])->format('F d, Y');
            $from = Carbon::parse($res["reservation At"])->format('Hi');
            $from = (int)$from; //930
            $to = $from + 30;
            if ($to % 100 == 60)
                $to += 40;
            $Appointment = array("date"=>$date,"from"=>$from,"to"=>$to);
            array_push($myAppointments,$Appointment);
            //echo $resvation->toFormattedDateString();
            //echo $resvation->hour;
        }

//        $myAppointments = [
//            [
//                'date'  =>  date("F d, Y"),
//                'from'  =>  '900',
//                'to'    =>  '930'
//            ]
//        ];
        echo json_encode($myAppointments);
    }

}
