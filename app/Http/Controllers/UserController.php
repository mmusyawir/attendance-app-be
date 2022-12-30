<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    use ImageStorage;

    /**
     * Construct
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'is_admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::query();

            return DataTables::eloquent($data)
                ->addColumn('action', function ($data) {
                    return view('layouts._action', [
                        'model' => $data,
                        'recap' => [
                            'id' => $data->id,
                        ],
                        'edit_url' => route('user.edit', $data->id),
                        'show_url' => route('user.show', $data->id),
                        'delete_url' => route('user.destroy', $data->id),
                    ]);
                })
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->toJson();
        }

        // $users = User::paginate(5);
        return view('pages.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.user.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $photo = $request->file('image');

        if ($photo) {
            $request['photo'] = $this->uploadImage($photo, $request->name, 'profile');
        }

        $request['password'] = Hash::make($request->password);

        User::create($request->all());

        return redirect()->route('user.index');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('pages.user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $photo = $request->file('image');

        if ($photo) {
            $request['photo'] = $this->uploadImage($photo, $request->name, 'profile', true, $user->photo);
        }

        if ($request->password) {
            $request['password'] = Hash::make($request->password);
        } else {
            $request['password'] = $user->password;
        }

        $user->update($request->all());

        return redirect()->route('user.index');
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);

            if ($user->photo)
                $this->deleteImage($user->photo, 'profile');

            foreach ($user->attendances()->get() as $attendance) {
                foreach ($attendance->detail()->get() as $attendanceDetail) {
                    $attendanceDetail->delete();
                }

                $attendance->delete();
            }

            $user->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('user.index');
    }

    public function recap(Request $request, $id)
    {
        $this->validate($request, [
            'start' => 'required|date',
            'end' => 'required|date',
        ]);


        $user = User::findOrFail($id);
        $data = $user->attendances()
            ->with('detail')
            ->whereBetween(DB::raw('DATE(created_at)'), [$request->start, $request->end])
            ->orderBy('created_at', 'DESC')
            ->get();

         $range = [
            'start' => $request->start,
            'end' => $request->end,
        ];

        $range = (object) $range;

        return view('pages.user.recap', compact('data', 'user', 'range'));
    }
}
