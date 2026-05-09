<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branch = $this->user()->branch;
        $requireGeo = $branch->require_geolocation ??false;
        $requirePhoto = $branch?->require_liveness ?? false;
        return [
            'latitude' => $requireGeo ? 'required|numeric' : 'nullable|numeric',
            'longitude' => $requireGeo ? 'required|numeric' : 'nullable|numeric',
            'photo' => $requirePhoto ? 'required|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude is required',
            'latitude.numeric' => 'Latitude must be a number',
            'longitude.required' => 'Longitude is required',
            'longitude.numeric' => 'Longitude must be a number',
            'photo.image' => 'Photo must be an image',
            'photo.mimes' => 'Photo must be a file of type: jpeg, png, jpg, gif',
            'photo.max' => 'Photo may not be greater than 2048 kilobytes',
        ];
    }
}
