<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSkillRequest;
use App\Http\Requests\UpdateSkillRequest;
use App\Http\Resources\SkillResource;

class SkillController extends Controller
{

    public function index()
    {
        $skills = Skill::all();

        return SkillResource::collection($skills);
    }

    public function store(StoreSkillRequest $request)
    {

        $skill_created = Skill::create($request->validated());

        return response()->json(
            [
                'message' => 'Skill created successfully!',
                'skill' => new SkillResource($skill_created)
            ]
        );
    }

    public function update(UpdateSkillRequest $request, Skill $skill)
    {
        if(!$request->name && !$request->slug){
            return response()->json(['error'=>"Please provide at least one field to be updated."], 422);
        }
        $skill_updated = $skill->update($request->validated());

        if ($skill_updated) {

            return response()->json(
                [
                    'message' => 'Skill updated successfully!'
                ]
            );
        } else {
            return response()->json(
                [
                    'message' => 'Skill updated failed!'
                ]
            );
        }
    }



    public function show(Skill $skill)
    {
        return new SkillResource($skill);

    }

    public function destroy(Skill $skill)
    {
        $isDeleted = $skill->delete();
        if($isDeleted){
            return response()->json(['success' => 'Skill deleted successfully!'], 200);
        }else{
            return response()->json(['error' => 'Skill deletion has failed!'], 500);

        }
    }
}
