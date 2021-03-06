<?php

namespace App\Http\Controllers;

use App\Enums\RenewalFrequencies;
use App\Http\Requests\RenewalFrequencyRequest;
use App\RenewalFrequency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class RenewalFrequencyController extends Controller
{

    public function __construct()
    {
        $this->middleware('onlyAjax', ['except' => 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(request()->wantsJson() || request()->expectsJson()) {
            $renewalFrequencies = Auth::user()->renewalFrequencies()->get();
            return DataTables::of($renewalFrequencies)
                ->editColumn('type', function ($renewal) {
                    return RenewalFrequencies::getDescription($renewal->type);
                })->addColumn('actions', function($renewalFrequency){
                    return implode("", [
                        '<a href="' . route('renewal-frequencies.edit', $renewalFrequency) . '" data-original-title="' . __('messages.edit_renewal_frequency') . '" class="edit btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill"><i class="la la-edit"></i></a>',
                        '<a href="' . route('renewal-frequencies.destroy', $renewalFrequency) . '" class="deleteDataTableRecord btn m-btn m-btn--hover-brand m-btn--icon m-btn--icon-only m-btn--pill"><i class="la la-trash"></i></a>',
                    ]);
                })->rawColumns(['actions'])->make(true);
        }

        $renewalFrequency = new RenewalFrequency;

        return view('renewalFrequencies.index', compact('renewalFrequency'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $renewalFrequency = new RenewalFrequency;

        return [
            'view' => view( 'renewalFrequencies.create', compact('renewalFrequency'))->render(),
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RenewalFrequencyRequest $request)
    {
        $renewal_frequency = auth()->user()->renewalFrequencies()->create($request->validated());

        return [
            'object' => [
                'id' => $renewal_frequency->id,
                'name' => $renewal_frequency->frequency
            ],
            'message' => __('messages.renewal_frequency_created_status')
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(RenewalFrequency $renewalFrequency)
    {
        $this->authorize('view', $renewalFrequency);

        return [
            'view' => view( 'renewalFrequencies.edit', compact('renewalFrequency'))->render(),
        ];

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RenewalFrequencyRequest $request, RenewalFrequency $renewalFrequency)
    {
        $renewalFrequency->update($request->validated());

        return [
            'message' => __('messages.renewal_frequency_update_status')
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(RenewalFrequency $renewalFrequency)
    {
        $this->authorize('delete', $renewalFrequency);
        $renewalFrequency->delete();

        return [
            'message' => trans_choice('messages.renewal_frequency_delete_status', 1)
        ];
    }

    public function destroyAll(Request $request)
    {
        $ids = explode(",",$request->ids);

        foreach ($ids as $id){
            Auth::user()->renewalFrequencies()->findOrFail($id)->delete();
        }

        return [
            'message' => trans_choice('messages.renewal_frequency_delete_status', count($ids))
        ];

    }
}
