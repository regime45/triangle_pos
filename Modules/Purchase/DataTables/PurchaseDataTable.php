<?php

namespace Modules\Purchase\DataTables;

use Modules\Purchase\Entities\Purchase;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PurchaseDataTable extends DataTable
{

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('total_amount', fn($data) => format_currency($data->total_amount))
            ->addColumn('paid_amount', fn($data) => format_currency($data->paid_amount))
            ->addColumn('due_amount', fn($data) => format_currency($data->due_amount))
            ->addColumn('status', fn($data) => view('purchase::partials.status', compact('data')))
            ->addColumn('payment_status', fn($data) => view('purchase::partials.payment-status', compact('data')))
            ->addColumn('action', fn($data) => view('purchase::partials.actions', compact('data')))

            // Aging by Purchase: days since rrDate
            ->addColumn('aging_purchase', function ($data) {
                if (!$data->rrDate) return '-';
                $rrDate = \Carbon\Carbon::parse($data->rrDate);
                return $rrDate->diffInDays(now());
            })

            ->addColumn('aging_terms', function ($data) {
                return optional($data->supplier)->terms ?? 0;
            })



            ->addColumn('rrDate', function ($data) {
                if (!$data->rrDate) return '-';
                // Format rrDate as 12-hour with AM/PM
                return \Carbon\Carbon::parse($data->rrDate)->format('Y-m-d h:i:s A');
            })
        ;
    }


    public function query(Purchase $model)
    {
        return $model->newQuery()->with('supplier');
    }



    public function html()
    {
        return $this->builder()
            ->setTableId('purchases-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4'f>> .
                                'tr' .
                                <'row'<'col-md-5'i><'col-md-7 mt-2'p>>")
            ->orderBy(1)
            ->buttons(
                Button::make('excel')
                    ->text('<i class="bi bi-file-earmark-excel-fill"></i> Excel'),
                Button::make('print')
                    ->text('<i class="bi bi-printer-fill"></i> Print'),
                Button::make('reset')
                    ->text('<i class="bi bi-x-circle"></i> Reset'),
                Button::make('reload')
                    ->text('<i class="bi bi-arrow-repeat"></i> Reload')
            );
    }

    protected function getColumns()
    {
        return [
            Column::make('reference')
                ->className('text-center align-middle'),

             Column::make('date')
                ->title('Order Date')
                ->className('text-center align-middle'),

            Column::make('supplier_name')
                ->title('Supplier')
                ->className('text-center align-middle'),

            Column::computed('status')
                ->className('text-center align-middle'),

            Column::computed('total_amount')
                ->className('text-center align-middle'),

            Column::computed('paid_amount')
                ->className('text-center align-middle'),

            Column::computed('due_amount')
                ->className('text-center align-middle'),

            Column::computed('aging_purchase')
                ->title('Aging By Purchase')
                ->className('text-center align-middle'),

            Column::computed('aging_terms')
                ->title('Aging By Terms')
                ->className('text-center align-middle'),

            Column::computed('rrDate')
                ->title('RR Date')
                ->className('text-center align-middle'),


            Column::computed('payment_status')
                ->className('text-center align-middle'),

            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->className('text-center align-middle'),

            Column::make('created_at')
                ->visible(false)
        ];
    }

    protected function filename(): string
    {
        return 'Purchase_' . date('YmdHis');
    }
}
