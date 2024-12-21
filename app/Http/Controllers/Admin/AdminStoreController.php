<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStoreRequest;
use App\Http\Requests\UpdateStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;

class AdminStoreController extends Controller
{
    public function getAllStores(){
        $stores = Store::all();
        return ResponseHelper::jsonResponse($stores ,
        __('message.get_all_stores'),200,true);
    }

    public function createNewStore(CreateStoreRequest  $request){
        $newStore = $request->validated();

        $store = Store::create([
            'name_en' => $newStore['name_en'],
            'name_ar' => $newStore['name_ar'],
            'image' => $newStore['image'],
            'category_id' => $newStore['category_id'],
            'location_en' => $newStore['location_en'],
            'location_ar' => $newStore['location_ar'],
        ]);

        return ResponseHelper::jsonResponse($store ,__('message.create_store'),200,true);

    }

    public function showStore(Store $store){

        return ResponseHelper::jsonResponse($store ,__('message.get_store'),200,true);
    }

    public function updateStore(UpdateStoreRequest $request, Store $store){
        $store = $request->validated();
        $updatestore = Store::update($store);
        return ResponseHelper::jsonResponse($updatestore ,__('message.update_store'),200,true);
    }

    public function destroyStore(Store $store){
        $store->delete();
        return ResponseHelper::jsonResponse($store ,__('message.delete_store'),200,true);
    }
}
