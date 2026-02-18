<div class="input-group d-flex justify-content-center align-items-center">
    <input wire:model="quantity.{{ $cart_item->id }}" 
           type="number" 
           style="min-width: 40px; max-width: 90px;"
           class="form-control"
           value="{{ $cart_item->qty }}" 
           min="1"
           @if(isset($status) && $status == 'Completed') readonly @endif
    >
    <div class="input-group-append">
        <button type="button" 
                wire:click="updateQuantity('{{ $cart_item->rowId }}', {{ $cart_item->id }})"
                class="btn btn-info"
                @if(isset($status) && $status == 'Completed') disabled @endif
        >
            <i class="bi bi-check"></i>
        </button>
    </div>
</div>
