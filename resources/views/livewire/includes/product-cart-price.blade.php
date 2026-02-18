<div class="input-group d-flex justify-content-center">
    <input wire:model="unit_price.{{ $cart_item->id }}" 
           style="min-width: 40px; max-width: 90px;" 
           type="text" 
           class="form-control" 
           min="0"
           @if(isset($status) && $status == 'Completed') readonly @endif
    >
    <div class="input-group-append">
        <button type="button" 
                wire:click="updatePrice('{{ $cart_item->rowId }}', {{ $cart_item->id }})"
                class="btn btn-info"
                @if(isset($status) && $status == 'Completed') disabled @endif
        >
            <i class="bi bi-check"></i>
        </button>
    </div>
</div>
