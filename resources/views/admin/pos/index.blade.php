@extends('admin.layout')

@section('sidebar', 'overlay-sidebar')

@section('styles')
<link rel="stylesheet" href="{{asset('assets/admin/css/calculator.min.css')}}">
@endsection

@section('content')

<div class="row" id="outsidePrintScreen">
    <div class="col-md-12">

        <div class="row">
            <div class="col-lg-5">
                <div class="row">
                    <div class="col-12 px-0">
                        <form>
                            <div class="form-group pt-0">
                                <input name="search" type="text" class="form-control" placeholder="Search by Item Name here...">
                            </div>
                        </form>
                    </div>
                </div>
                <div id="posCatItems" style="display: block;">
                    
                </div>
                <div id="posItems" style="display: none;">
                    @includeIf('admin.pos.partials.items')
                </div>
            </div>
            <div class="col-lg-2">
                <div class="card">
                    <div class="card-body px-2">
                        <form id="orderForm" action="{{route('admin.pos.placeOrder')}}" method="POST">
                            @csrf
                            <div class="form-group p-0 pb-2">
                                <div class="ui-widget">
                                    <label for="">Customer Phone</label>
                                    <input class="form-control" type="text" name="customer_phone" placeholder="Customer Phone Number" value="{{old('customer_phone')}}" onchange="loadCustomerName(this.value)">
                                    <p class="text-warning mb-0">Use <strong>Country Code</strong> in phone number</p>
                                </div>
                            </div>
                            <div class="form-group p-0 pb-2">
                                <div class="ui-widget">
                                    <label for="">Customer Name</label>
                                    <input class="form-control" name="customer_name" type="text" placeholder="Customer Name" value="{{old('customer_name')}}" disabled>
                                    <small class="text-warning">Enter customer phone first.</small>
                                </div>
                            </div>
                            <div class="form-group p-0 pb-2">
                                <label for="">Serving Method **</label>
                                <select class="form-control" name="serving_method" required>
                                    @foreach ($smethods as $smethod)
                                        <option value="{{$smethod->value}}" {{$smethod->value == old('serving_method') ? 'selected' : ''}}>{{$smethod->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group p-0 pb-2">
                                <label for="">Payment Method </label>
                                <select class="form-control select2" name="payment_method">
                                    <option value="" selected disabled>Select Payment Method</option>
                                    @foreach ($pmethods as $pmethod)
                                        <option value="{{$pmethod->name}}" {{$pmethod->name == old('payment_method') ? 'selected' : ''}}>{{$pmethod->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group p-0 pb-2">
                                <label for="">Payment Status **</label>
                                <select class="form-control" name="payment_status" required>
                                    <option value="Pending" {{"Pending" == old('payment_status') ? 'selected' : ''}}>Pending</option>
                                    <option value="Completed" {{"Completed" == old('payment_status') ? 'selected' : ''}}>Completed</option>
                                </select>
                            </div>
                            <div id="on_table" class="d-none extra-fields">
                                <div class="form-group p-0 pb-2">
                                    <label for="">Table No</label>
                                    <select class="form-control select2" name="table_no">
                                        <option value="" selected disabled>Select Table No</option>
                                        @foreach ($tables as $table)
                                        @php
                                            if($table->is_busy == 1){
                                                $table_no = '';
                                                $avalied = 'Busy';
                                            }else{
                                                $table_no = $table->table_no;
                                                $avalied = 'Available';
                                            }
                                        @endphp
                                            <option value="{{$table_no}}" {{$table->table_no == old('table_no') ? 'selected' : ''}}>Table - {{$table->table_no}} Is {{$avalied}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div id="pick_up" class="d-none extra-fields">
                                <div class="form-group p-0 pb-2">
                                    <label for="">Pickup Date</label>
                                    <input name="pick_up_date" type="text" class="form-control datepicker" placeholder="Pickup Date" autocomplete="off">
                                </div>
                                <div class="form-group p-0 pb-2">
                                    <label for="">Pickup Time</label>
                                    <input name="pick_up_time" type="text" class="form-control timepicker" placeholder="Pickup Time" autocomplete="off">
                                </div>
                            </div>

                            <div id="home_delivery" class="d-none extra-fields">
                                @if ($be->delivery_date_time_status == 1)
                                    <div class="form-group p-0 pb-2">
                                        <label>Delivery Date</label>
                                        <div class="field-input cross {{!empty(old('delivery_date')) ? 'cross-show' : ''}}">
                                            <input class="form-control delivery-datepicker" type="text" name="delivery_date" autocomplete="off" value="{{old('delivery_date')}}">
                                            <i class="far fa-times-circle"></i>
                                        </div>
                                    </div>
                                    <div class="form-group p-0 pb-2">
                                        <label>Delivery Time</label>
                                        <select id="deliveryTime" class="form-control" name="delivery_time" disabled>
                                            <option value="" selected disabled>Select a time frame</option>
                                        </select>
                                    </div>
                                @endif

                                <div id="shippingPostCharges">
                                    @if ($bs->postal_code == 0)
    
                                        @if (count($scharges) > 0)
                                            <div id="shippingCharges" class="form-group p-0 pb-2">
                                                <label>{{__('Shipping Charges')}}</label>
                                                @foreach ($scharges as $scharge)
                                                    <div class="form-check p-0 pl-4">
                                                        <input class="form-check-input" type="radio" data="{{!empty($scharge->free_delivery_amount) && (posCartSubTotal() >= $scharge->free_delivery_amount) ? 0 : $scharge->charge}}" name="shipping_charge" id="scharge{{$scharge->id}}" value="{{$scharge->id}}" {{$loop->first ? 'checked' : ''}} data-free_delivery_amount="{{$scharge->free_delivery_amount}}">
                                                        <label class="form-check-label mb-0" for="scharge{{$scharge->id}}">{{$scharge->title}}</label>
                                                        +
                                                        <strong>
                                                            {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}{{$scharge->charge}}{{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                                        </strong>
                                                    </div>
    
                                                    @if (!empty($scharge->free_delivery_amount))
                                                        <p class="mb-0 pl-2">(@lang('Free Delivery for Orders over') 
                                                            {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}{{$scharge->free_delivery_amount - 1}}{{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}})</p>
                                                    @endif
                                                    <p class="mb-0 text-warning pl-2"><small>{{$scharge->text}}</small></p>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <div class="form-group p-0 pb-2">
                                            <label>{{__('Postal Code')}} (Delivery Area)</label>
                                            <select name="postal_code" class="select2 form-control">
                                                @foreach ($postcodes as $postcode)
                                                    <option value="{{$postcode->id}}" data="{{$postcode->charge}}" data-free_delivery_amount="{{$postcode->free_delivery_amount}}">
                                                        @if (!empty($postcode->title))
                                                            {{$postcode->title}} -
                                                        @endif
                                                        {{$postcode->postcode}}
                        
                                                        ({{__('Delivery Charge')}} - {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}{{$postcode->charge}}{{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}} 
                                                        @if (!empty($postcode->free_delivery_amount))
                                                            ,  @lang('Free Delivery for Orders over') 
                                                            {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}{{$postcode->free_delivery_amount - 1}}{{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                                        @endif
                                                        )
                        
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="card-footer text-center">
                        <button form="orderForm" class="btn btn-success" type="submit">Place Order</button>
                        @if ($onTable->pos == 1)
                            <p class="mb-0 text-warning">Token No. print option (for '{{$onTable->name}}' orders) will be shown after placing order.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h4 class="text-white">Ordered Foods</h4>
                            </div>
                        </div>


                        <div id="divRefresh">
                            @if (empty($cart))
                            <div class="text-center py-5 bg-dark mt-4">
                                <h4>NO ITEMS ADDED</h4>
                            </div>
                            @else
                            <div id="cartTable">

                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-white">Item</th>
                                            <th scope="col" class="text-white">Qty</th>
                                            <th scope="col" class="text-white">Price ({{$be->base_currency_symbol}})</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cart as $key => $item)
                                        @php
                                        $id = $item["id"];
                                        $product = App\Models\Product::findOrFail($id);
                                        @endphp
                                        <tr class="cart-item">
                                            <td width="45%" class="item">
                                                <h5 class="text-white">{{convertUtf8($item['name'])}}</h5>
                                                @if (!empty($item["variations"]))
                                                    <p><strong class="text-white">{{__("Variation")}}:</strong> <br>
                                                        @php
                                                            $variations = $item["variations"];
                                                        @endphp
                                                        @foreach ($variations as $vKey => $variation)
                                                            <span class="text-capitalize">{{str_replace("_"," ",$vKey)}}:</span> {{$variation["name"]}}
                                                            @if (!$loop->last)
                                                            ,
                                                            @endif
                                                        @endforeach    
                                                    </p>
                                                @endif
                                                
                                                @if (!empty($item["addons"]))
                                                <p>
                                                    <strong class="text-white">{{__("Add On's")}}:</strong>
                                                    @php
                                                    $addons = $item["addons"];
                                                    @endphp
                                                    @foreach ($addons as $addon)
                                                    {{$addon["name"]}}
                                                    @if (!$loop->last)
                                                    ,
                                                    @endif
                                                    @endforeach
                                                </p>
                                                @endif
                                                <i class="fas fa-times text-danger item-remove" data-href="{{route('admin.cart.item.remove',$key)}}"></i>
                                            </td>
                                            <td width="45%" style="padding-left: 0px !important;padding-right: 0px !important;">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text sub" data-key="{{$key}}">
                                                            <i class="fas fa-minus"></i>
                                                        </span>
                                                    </div>
                                                    <input name="quantity" type="number" class="form-control" value="{{$item['qty']}}" data-key="{{$key}}">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text add" data-key="{{$key}}">
                                                            <i class="fas fa-plus"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td width="10%">
                                                {{$item['total']}}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Subtotal
                                    <span>
                                        {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}
                                        <span id="subtotal">{{posCartSubTotal()}}</span>
                                        {{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Tax
                                    <span>
                                        +
                                        {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}
                                        <span id="tax">{{posTax()}}</span>
                                        {{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Shipping Charge
                                    <span>
                                        +
                                        {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}
                                        <span id="shipping">{{posShipping()}}</span>
                                        {{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center bg-primary text-white">
                                    <strong>Total</strong>
                                    <span>
                                        {{$be->base_currency_symbol_position == 'left' ? $be->base_currency_symbol : ''}}
                                        <span class="grandTotal">{{posCartSubTotal() + posTax() + posShipping()}}</span>
                                        {{$be->base_currency_symbol_position == 'right' ? $be->base_currency_symbol : ''}}
                                    </span>
                                </li>
                            </ul>

                            @endif
                        </div>
                    </div>

                    <div class="card-footer text-center">
                        <div class="row no-gutters">
                            <div class="col-lg-4">
                                <button id="calcModalBtn" type="button" class="btn btn-primary btn-block" data-toggle="tooltip" data-placement="bottom" title="Calculator">
                                    <i class="fas fa-calculator"></i> Calculator
                                </button>
                            </div>
                            <div class="col-lg-4">
                                <button class="btn btn-success btn-block" id="printBtn">Print Receipt</button>
                            </div>
                            <div class="col-lg-4">
                                <button class="btn btn-danger btn-block" id="clearCartBtn">Clear Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
              <div class="col-lg-12">
                @if (count($orders) == 0)
                  <h3 class="text-center">NO ORDER FOUND</h3>
                @else
                  <div id="refreshOrder">
                      <div class="table-responsive">
                        <div id="orders-table">
                        <table class="table table-striped mt-3">
                          <thead>
                            <tr>
                              <th scope="col">
                                  <input type="checkbox" class="bulk-check" data-val="all">
                              </th>
  
                              <th scope="col">Order Number</th>
                              <th scope="col">Serving Method</th>
                              <th scope="col">Payment</th>
                              <th scope="col">Status</th>
                              <th scope="col">Completed</th>
                              <th scope="col">Gateway</th>
                              <th scope="col">Time</th>
                              <th scope="col">Actions</th>
                            </tr>
                          </thead>
  
                          <tbody>
                            @foreach ($orders as $key => $order)
                              <tr>
                                <td>
                                  <input type="checkbox" class="bulk-check" data-val="{{$order->id}}">
                                </td>
                                <td>{{$order->order_number}}</td>
                                <td class="text-capitalize">
                                    @if ($order->serving_method == 'on_table')
                                        On Table
                                    @elseif ($order->serving_method == 'home_delivery')
                                        Home Delivery
                                    @elseif ($order->serving_method == 'pick_up')
                                        Pick up
                                    @endif
                                </td>
                                <td>
                                  @if ($order->type == 'pos' || $order->gateway_type == 'offline')
                                      <form id="paymentForm{{$order->id}}" class="d-inline-block" action="{{route('admin.product.order.payment')}}" method="post">
                                          @csrf
                                          <input type="hidden" name="order_id" value="{{$order->id}}">
                                          <select class="form-control form-control-sm
                                              @if ($order->payment_status == 'Pending')
                                                  bg-warning
                                              @elseif ($order->payment_status == 'Completed')
                                                  bg-success
                                              @endif
                                          " name="payment_status" onchange="document.getElementById('paymentForm{{$order->id}}').submit();">
                                                  <option value="Pending" {{$order->payment_status == 'Pending' ? 'selected' : ''}}>Pending</option>
                                                  <option value="Completed" {{$order->payment_status == 'Completed' ? 'selected' : ''}}>Completed</option>
                                          </select>
                                      </form>
                                  @else
                                      @if ($order->payment_status == 'Pending' || $order->payment_status == 'pending')
                                          <p class="badge badge-danger">Pending</p>
                                      @else
                                          <p class="badge badge-success">Completed</p>
                                      @endif
                                  @endif
                                </td>
                                <td>
                                  <form id="statusForm{{$order->id}}" class="d-inline-block" action="{{route('admin.product.orders.status')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="order_id" value="{{$order->id}}">
                                    <select class="form-control
                                    @if ($order->order_status == 'pending')
  
                                    @elseif ($order->order_status == 'received')
                                      bg-secondary
                                    @elseif ($order->order_status == 'preparing')
                                      bg-warning
                                    @elseif ($order->order_status == 'ready_to_pick_up')
                                      bg-primary
                                    @elseif ($order->order_status == 'picked_up')
                                      bg-info
                                    @elseif ($order->order_status == 'delivered')
                                      bg-success
                                    @elseif ($order->order_status == 'cancelled')
                                      bg-danger
                                    @elseif ($order->order_status == 'ready_to_serve')
                                      bg-white text-dark
                                    @elseif ($order->order_status == 'served')
                                      bg-light text-dark
                                    @endif
                                    " name="order_status" onchange="document.getElementById('statusForm{{$order->id}}').submit();">
                                      <option value="pending" {{$order->order_status == 'pending' ? 'selected' : ''}}>Pending</option>
                                      <option value="received" {{$order->order_status == 'received' ? 'selected' : ''}}>Received</option>
                                      <option value="preparing" {{$order->order_status == 'preparing' ? 'selected' : ''}}>Preparing</option>
  
                                      @if ($order->serving_method != 'on_table')
                                      <option value="ready_to_pick_up" {{$order->order_status == 'ready_to_pick_up' ? 'selected' : ''}}>Ready to pick up</option>
                                      <option value="picked_up" {{$order->order_status == 'picked_up' ? 'selected' : ''}}>Picked up</option>
                                      @endif
  
                                      @if ($order->serving_method == 'home_delivery')
                                      <option value="delivered" {{$order->order_status == 'delivered' ? 'selected' : ''}}>Delivered</option>
                                      @endif
  
                                      @if ($order->serving_method == 'on_table')
                                      <option value="ready_to_serve" {{$order->order_status == 'ready_to_serve' ? 'selected' : ''}}>Ready to Serve</option>
                                      <option value="served" {{$order->order_status == 'served' ? 'selected' : ''}}>Served</option>
                                      @endif
  
                                      <option value="cancelled" {{$order->order_status == 'cancelled' ? 'selected' : ''}}>Cancelled</option>
                                    </select>
                                  </form>
                                </td>
                                <td>
                                  <form id="completeForm{{$order->id}}" class="d-inline-block" action="{{route('admin.product.order.completed')}}" method="post">
                                      @csrf
                                      <input type="hidden" name="order_id" value="{{$order->id}}">
                                      <select class="form-control
                                          @if (empty($order->completed) || $order->completed == 'no')
                                              bg-danger
                                          @elseif ($order->completed == 'yes')
                                              bg-success
                                          @endif
                                      " name="completed" onchange="document.getElementById('completeForm{{$order->id}}').submit();">
                                              <option value="no" {{empty($order->completed) || $order->completed == 'no' ? 'selected' : ''}}>No</option>
                                              <option value="yes" {{$order->completed == 'yes' ? 'selected' : ''}}>Yes</option>
                                      </select>
                                    </form>
                                </td>
                                <td class="text-capitalize">
                                    {{$order->method}}
                                </td>
                                <td>
                                    {{$order->created_at}}
                                </td>
  
                                <td>
                                  <div class="dropdown">
                                      <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Actions
                                      </button>
                                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="{{route('admin.product.details',$order->id)}}">Details</a>
  
                                        <a href="#" class="dropdown-item editbtn" data-toggle="modal" data-target="#mailModal" data-email="{{$order->billing_email}}">Send Mail</a>
  
                                        @if ($order->type != 'pos')
                                        <a class="dropdown-item" href="{{asset('assets/front/invoices/product/'.$order->invoice_number)}}">Invoice</a>
                                        @endif
  
                                        <a class="dropdown-item" href="#">
                                          <form class="deleteform d-inline-block" action="{{route('admin.product.order.delete')}}" method="post">
                                              @csrf
                                              <input type="hidden" name="order_id" value="{{$order->id}}">
                                              <button type="submit" class="deletebtn">
                                                Delete
                                              </button>
                                          </form>
                                        </a>
                                      </div>
                                  </div>
  
                                </td>
                              </tr>
  
                            @endforeach
                          </tbody>
                        </table>
                        </div>
                      </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="row">
              <div class="d-inline-block mx-auto">
                {{$orders->appends(['orders_from' => request()->input('orders_from'), 'serving_method' => request()->input('serving_method'), 'order_status' => request()->input('order_status'), 'payment_status' => request()->input('payment_status'), 'completed' => request()->input('completed'), 'order_date' => request()->input('order_date'), 'delivery_date' => request()->input('delivery_date'), 'search' => request()->input('search')])->links()}}
              </div>
            </div>
          </div>
    </div>
</div>

{{-- Calculator Modal --}}
<div class="modal fade" id="calcModal" tabindex="-1" role="dialog" aria-labelledby="calcModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Calculator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container">


                    <form>
                        <input readonly id="display1" type="text" class="form-control-lg text-right">
                        <input readonly id="display2" type="text" class="form-control-lg text-right">
                    </form>

                    <div class="d-flex justify-content-between button-row">
                        <button id="left-parenthesis" type="button" class="operator-group">&#40;</button>
                        <button id="right-parenthesis" type="button" class="operator-group">&#41;</button>
                        <button id="square-root" type="button" class="operator-group">&#8730;</button>
                        <button id="square" type="button" class="operator-group">&#120;&#178;</button>
                    </div>

                    <div class="d-flex justify-content-between button-row">
                        <button id="clear" type="button">&#67;</button>
                        <button id="backspace" type="button">&#9003;</button>
                        <button id="ans" type="button" class="operand-group">&#65;&#110;&#115;</button>
                        <button id="divide" type="button" class="operator-group">&#247;</button>
                    </div>


                    <div class="d-flex justify-content-between button-row">
                        <button id="seven" type="button" class="operand-group">&#55;</button>
                        <button id="eight" type="button" class="operand-group">&#56;</button>
                        <button id="nine" type="button" class="operand-group">&#57;</button>
                        <button id="multiply" type="button" class="operator-group">&#215;</button>
                    </div>


                    <div class="d-flex justify-content-between button-row">
                        <button id="four" type="button" class="operand-group">&#52;</button>
                        <button id="five" type="button" class="operand-group">&#53;</button>
                        <button id="six" type="button" class="operand-group">&#54;</button>
                        <button id="subtract" type="button" class="operator-group">&#8722;</button>
                    </div>


                    <div class="d-flex justify-content-between button-row">
                        <button id="one" type="button" class="operand-group">&#49;</button>
                        <button id="two" type="button" class="operand-group">&#50;</button>
                        <button id="three" type="button" class="operand-group">&#51;</button>
                        <button id="add" type="button" class="operator-group">&#43;</button>
                    </div>

                    <!-- Rounded switch -->
                    <label class="switch" style="display: none;">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                    <div class="d-flex justify-content-between button-row">
                        <button id="percentage" type="button" class="operand-group">
                            {{-- &#37; --}}

                        </button>
                        <button id="zero" type="button" class="operand-group">&#48;</button>
                        <button id="decimal" type="button" class="operand-group">&#46;</button>
                        <button id="equal" type="button">&#61;</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

    {{-- Variation Modal Starts --}}
    @includeIf('front.partials.variation-modal')
    {{-- Variation Modal Ends --}}

    <div id="customerCopy">
        <iframe id="customerReceipt" src="{{url("admin/print/customer-copy")}}" style="display:none;"></iframe>
    </div>
    <div id="kitchenCopy">
        <iframe id="kitchenReceipt" src="{{url("admin/print/kitchen-copy")}}" style="display:none;"></iframe>
    </div>
    <div id="tokenNo">
        <iframe id="tokenNoPrintable" src="{{url("admin/print/token-no")}}" style="display:none;"></iframe>
    </div>
@endsection



@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/mathjs/3.17.0/math.min.js"></script>
<script src="{{asset('assets/admin/js/plugin/calculator/calculator.min.js')}}"></script>
<script src="{{asset('assets/admin/js/plugin/printthis.min.js')}}"></script>

@if (Session::has('success') && $onTable->pos == 1 && Session::has('previous_serving_method') && Session::get('previous_serving_method') == 'on_table')
<script>
    var tokenFrame = document.getElementById("tokenNoPrintable");
    tokenFrame.focus();  
    tokenFrame.contentWindow.print();
</script>
@endif
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function fetchOrders() {
    $.ajax({
        url: "{{ route('admin.product.orders') }}",
        type: "GET",
        success: function(data) {
            $('#orders-table').html(data);
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
        }
    });
}

</script>
{{-- Blade syntax for the JavaScript code --}}
<script>
    $(document).ready(function() {
        // Function to load extra fields based on selected serving method
        function loadExtraFields(val) {
            $(".extra-fields").removeClass('d-block').addClass('d-none').attr('disabled', true);
            $("#" + val).removeClass("d-none").addClass("d-block").removeAttr('disabled');
        }

        // Function to set shipping charge based on serving method
        function setShippingCharge() {
            var servingMethod = $("select[name='serving_method']").val();
            var charge = 0.00;
            
            if (servingMethod == 'home_delivery') {
                @if ($bs->postal_code == 0)
                    var $checkedIn = $("input[name='shipping_charge']:checked");
                    charge = $checkedIn.length > 0 ? parseFloat($checkedIn.attr('data')) : 0;
                    if ($checkedIn.data('free_delivery_amount') && (parseFloat($("#subtotal").text()) >= parseFloat($checkedIn.data('free_delivery_amount')))) {
                        charge = 0;
                    }
                @else
                    var $selectedOpt = $("select[name='postal_code']").children('option:selected');
                    charge = $selectedOpt.data('free_delivery_amount') && (parseFloat($("#subtotal").text()) >= parseFloat($selectedOpt.data('free_delivery_amount'))) ? 0 : parseFloat($selectedOpt.attr('data'));
                @endif
            }
            
            $.get("{{route('admin.pos.shippingCharge')}}", {
                shipping_charge: charge,
                serving_method: servingMethod
            }, function(data) {
                $("#customerCopy").load(location.href + " #customerCopy");
                $("#divRefresh").load(location.href + " #divRefresh", function() {
                    $(".request-loader").removeClass('show');
                });
            });
        }

        // Load extra fields based on initially selected serving method
        loadExtraFields($("select[name='serving_method']").val());
        
        // Event listener for serving method change
        $("select[name='serving_method']").on('change', function() {
            loadExtraFields($(this).val());
            setShippingCharge();
        });

        // Event listener for clearing the cart
        $(document).on("click", "#clearCartBtn", function() {
            $(".request-loader").addClass("show");
            $.get("{{route('admin.cart.clear')}}", function(data) {
                if(data == "success") {
                    location.reload();
                }
            });
        });

        // Event listener for input search
        $(document).on("input", "input[name='search']", function() {
            var keyword = $(this).val().toLowerCase();
            if(keyword.length > 0) {
                $("#posCatItems").hide();
                $("#posItems").show();
                $(".pos-item").hide().each(function() {
                    var title = $(this).data('title').toLowerCase();
                    if(title.indexOf(keyword) > -1) {
                        $(this).show();
                    }
                });
            } else {
                $("#posItems").hide();
                $("#posCatItems").show();
            }
        });

        // Event listener for print button
        $("#printBtn").click(function() {
            var customerFrame = document.getElementById("customerReceipt");
            customerFrame.focus();
            customerFrame.contentWindow.print();

            var kitchenFrame = document.getElementById("kitchenReceipt");
            kitchenFrame.focus();
            kitchenFrame.contentWindow.print();
        });

        // Event listener for modal button
        $("#calcModalBtn").on('click', function() {
            $("#calcModal").modal('show');
        });

        // Event listener for loading customer name
        $(document).on("input", "input[name='customer_phone']", function() {
            loadCustomerName($(this).val());
        });
    });

    // Function to load customer name based on phone number
    function loadCustomerName(phone) {
        if(phone.length > 0) {
            $(".request-loader").addClass('show');
            $("input[name='customer_name']").removeAttr('disabled');
            $.get("load/" + phone + "/customer-name", function(data) {
                $(".request-loader").removeClass('show');
                $("input[name='customer_name']").val(data.name);
            });
        } else {
            $("input[name='customer_name']").val('').attr('disabled', true);
        }
    }

    // Function to load time frames based on selected date
    function loadTimeFrames(date, time) {
        if (date.length > 0) {
            $.get(
                "{{route('front.timeframes')}}",
                { date: date },
                function(data) {
                    console.log('time frames', data);
                    var options = `<option value="" selected disabled>Select a Time Frame</option>`;
                    if (data.status == 'success') {
                        $("#deliveryTime").removeAttr('disabled');
                        var timeframes = data.timeframes;
                        for (var i = 0; i < timeframes.length; i++) {
                            options += `<option value="${timeframes[i].id}" ${time == timeframes[i].id ? 'selected' : ''}>${timeframes[i].start_time} - ${timeframes[i].end_time}</option>`;
                        }
                    } else {
                        options += `<option value="">No time frames available</option>`;
                        $("#deliveryTime").attr('disabled', true);
                    }
                    $("#deliveryTime").html(options);
                }
            );
        } else {
            $("#deliveryTime").attr('disabled', true).html('<option value="" selected disabled>Select a Date First</option>');
        }
    }
</script>
{{-- END: Home delivery extra fields javascript --}}

<script>
    var textPosition = "{{$be->base_currency_text_position}}";
    var currText = "{{$be->base_currency_text}}";
    var posAudio = new Audio("{{asset('assets/front/files/beep-07.mp3')}}");
    var select = "{{__('Select')}}";
</script>
<!--====== Cart js ======-->
<script src="{{asset('assets/admin/js/cart.js')}}"></script>
@endsection