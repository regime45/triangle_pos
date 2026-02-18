<?php

namespace Modules\Sale\DataTables;

use Modules\Sale\Entities\Sale;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SalesDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('total_amount', function ($data) {
                return format_currency($data->total_amount +  $data->discount_percentage +  $data->discount_amount); // no division
            })
            ->addColumn('paid_amount', function ($data) {
                return format_currency($data->paid_amount);
            })
            ->addColumn('due_amount', function ($data) {
                return $data->due_amount > 0 ? format_currency($data->due_amount) : '₱0.00';
            })
            ->addColumn('change_amount', function ($data) {
                return $data->change_amount > 0 ? format_currency($data->change_amount) : '₱0.00';
            })
            ->addColumn('status', function ($data) {
                return view('sale::partials.status', compact('data'));
            })
            ->addColumn('payment_status', function ($data) {
                return view('sale::partials.payment-status', compact('data'));
            })
            ->addColumn('action', function ($data) {
                return view('sale::partials.actions', compact('data'));
            })

            ->addColumn('discount', function ($data) {
                // If percentage discount exists and > 0
                if (!is_null($data->discount_percentage) && $data->discount_percentage > 0) {
                    return format_currency($data->discount_percentage) . '';
                }

                // Else fallback to fixed amount discount
                if (!is_null($data->discount_amount) && $data->discount_amount > 0) {
                    return format_currency($data->discount_amount);
                }

                return '₱0.00';
            });
    }

    public function query(Sale $model)
    {
        return $model->newQuery()->orderBy('created_at', 'desc');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('sales-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-3'l><'col-md-5 mb-2'B><'col-md-4'f>>" .
                "tr" .
                "<'row'<'col-md-5'i><'col-md-7 mt-2'p>>")
            ->orderBy(0)
            ->buttons(
                Button::make('excel')->text('<i class="bi bi-file-earmark-excel-fill"></i> Excel'),
                Button::make('print')->text('<i class="bi bi-printer-fill"></i> Print'),
                Button::make('reset')->text('<i class="bi bi-x-circle"></i> Reset'),
                Button::make('reload')->text('<i class="bi bi-arrow-repeat"></i> Reload')
            );
    }

    protected function getColumns()
    {
        return [
            Column::make('reference')->className('text-center align-middle'),
            Column::make('customer_name')->title('Customer')->className('text-center align-middle'),
            Column::computed('status')->className('text-center align-middle'),
            Column::computed('total_amount')->className('text-center align-middle'),
           Column::computed('discount')
            ->title('Discount')
            ->className('text-center align-middle'),

            Column::computed('paid_amount')->className('text-center align-middle'),
            Column::computed('due_amount')->title('Due Amount')->className('text-center align-middle'),
            Column::computed('change_amount')->className('text-center align-middle'),
             Column::computed('payment_method')->title('Mode Of Payment')->className('text-center align-middle'),
           
            Column::computed('payment_status')->className('text-center align-middle'),
            Column::computed('action')->exportable(false)->printable(false)->className('text-center align-middle'),
          
        ];
    }

    protected function filename(): string
    {
        return 'Sales_' . date('YmdHis');
    }
}
