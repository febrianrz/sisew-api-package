<?php

namespace App\Http\Controllers\Administration\Merchant;
use App\Toko;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Forms\Builders\MerchantForm;

class MerchantController extends Controller
{

    
    public function create(MerchantForm $form)
    {
        return ['form' => $form->create()];
    }

    public function store(ValidateOwnerRequest $request, Toko $owner)
    {
        $owner = $owner->storeWithRoles(
            $request->all(),
            $request->get('roleList')
        );

        return [
            'message' => __('The owner was successfully created'),
            'redirect' => 'administration.owners.edit',
            'id' => $owner->id,
        ];
    }

    public function edit(Toko $owner, MerchantForm $form)
    {
        return ['form' => $form->edit($owner)];
    }

    public function update(ValidateOwnerRequest $request, Toko $owner)
    {
        $owner->updateWithRoles(
            $request->all(),
            $request->get('roleList')
        );

        return ['message' => __('The owner was successfully updated')];
    }

    public function destroy(Toko $owner)
    {
        $owner->delete();

        return [
            'message' => __('The owner was successfully deleted'),
            'redirect' => 'administration.owners.index',
        ];
    }
}
