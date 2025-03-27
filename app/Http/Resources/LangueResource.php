<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LangueResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => trans("message.{$this->name}"),
            'code' => $this->code,
            'flag' => $this->flag,
        ];

        // if ($request->isMethod('post')) {
        //     $response = [
        //         'success_message' => $this->successMessage($request),
        //         'success' => true,
        //         'data' => $data,
        //     ];
        // } else {
        //     $response = $data;
        // }

        return $data;
    }
}
