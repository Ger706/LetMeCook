<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use Illuminate\Http\Request;
use Mockery\Exception;

class ExpertController extends ResponseController
{
    public function createExpert(Request $request) {
        try {
            $data = $request->all();
            $expert = new Expert();
            $data['specialization'] = json_encode($data['specialization']);
            $expert->fill($data);
            $expert->save();

        } catch (Exception $e) {
            return $this->sendError('Failed to create Expert');
        }
        return $this->sendSuccess('Expert created successfully');
    }

    public function getAllExperts(Request $request) {
        try {
            $results = Expert::whereNull('deleted_at')->get()->toArray();
            if (!$results) {
                return $this->sendError('There Is No Expert');
            }
            foreach ($results as $key => $value) {
                $results[$key]['specialization'] = json_decode($value['specialization']);
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to get Expert');
        }
        return $this->sendResponseData($results);
    }

    public function getExpertDetail($expertId) {
        try {
            $result = Expert::find($expertId);
            if (!$result) {
                return $this->sendError('Expert not found');
            }
            $result['specialization'] = json_decode($result['specialization']);
        } catch (Exception $e) {
            return $this->sendError('Failed to get Expert');
        }
        return $this->sendResponseData($result);

    }
}
