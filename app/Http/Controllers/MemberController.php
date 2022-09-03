<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Get all members
        $members = Member::where('user_id', $request->user_id)->get();

        return response()->json(['status' => "OK", 'data' => $members], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Create new member
        $member = new Member;
        $member->name = $request->input('name');
        $member->user_id = $request->user_id;
        
        // Save to db
        $member->save();

        // save to redis
        Redis::set('member_' . $member->id, (string)$member);

        return response()->json(['status' => "OK", 'data' => $member], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        // Get one member from redis
        $memberFromRedis = Redis::get("member_". $id);

        if ($memberFromRedis) {
            // Validate member
            if ($member->user_id != $request->user_id) {
                abort(404);
            }

            // Get one member from db
            $member = json_decode($memberFromRedis);

            return response()->json(['status' => "OK", 'data' => $member], 200);
        }

        // Get one member from db
        $member = Member::where('id', $id)->where('user_id', $request->user_id)->first();

        // If member not found
        if ($member == null) {
            abort(404);
        }

        // Save to redis
        Redis::set('member_' . $member->id, (string)$member);

        return response()->json(['status' => "OK", 'data' => $member], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit(Member $member)
    {
        // 
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Get member
        $member = Member::where("id", $id)->where("user_id", $request->user_id)->first();

        // If member not found
        if ($member == null) {
            abort(404);
        }

        // Save to db
        $member->name = $request->input('name');
        $member->save();

        // Save to redis
        Redis::set('member_' . $member->id, (string)$member);

        return response()->json(['status' => "OK", 'data' => $member], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Get member
        $member = Member::where('id', $id)->where('user_id', $request->user_id)->first();

        // If member not found
        if ($member == null) {
            abort(404);
        }

        // Delete the member from db
        $member->delete();

        // Delete member from redis
        Redis::delete('member_'. $id);

        return response()->json(['status' => "OK", 'data' => null], 200);
    }
}
