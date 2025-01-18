<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminStoreController extends Controller
{
    public function getAllStores(){
        $stores = Store::all();
        return ResponseHelper::jsonResponse(StoreResource::collection($stores) ,
        __('message.get_all_stores'),200,true);
    }

    public function createNewStore(CreateStoreRequest  $request){
        $newStore = $request->validated();
        $categoryMapping = [
            'Restaurant' => 1,
            'Perfumes' => 2,
            'Clothes' => 3,
            'Electronics' => 4,
        ];

        $categoryId = $categoryMapping[$newStore['category']];
        if ($request->hasFile('image')) {
            $newStore['image'] = $request->file('image')->store('Store_image');
        }
        $store = Store::create([
            'name_en' => $newStore['name_en'],
            'name_ar' => $newStore['name_ar'],
            'image' => $newStore['image'],
            'category_id' => $categoryId,
            'location_en' => $newStore['location_en'],
            'location_ar' => $newStore['location_ar'],
        ]);

        return ResponseHelper::jsonResponse(StoreResource::make($store) ,__('message.create_store'),200,true);

    }

    public function showStore(Store $store){

        return ResponseHelper::jsonResponse(StoreResource::make($store)
            ,__('message.get_store'),200,true);
    }

    public function updateStore(UpdateStoreRequest $request, Store $store)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($store->image && Storage::exists($store->image)) {
                Storage::delete($store->image);
            }
            $validatedData['image'] = $request->file('image')->store('Store_image', 'public');
        }

        $store->update($validatedData);

        return ResponseHelper::jsonResponse(StoreResource::make($store), __('message.update_store'), 200, true);
    }


    public function destroyStore(Store $store){
        $store->delete();
        return ResponseHelper::jsonResponse(null ,__('message.delete_store'),200,true);
    }
}
