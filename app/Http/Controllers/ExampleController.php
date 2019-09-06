<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  public function showProfile()
  {
    return response()->json(['name' => 'Abigail', 'state' => 'CA']);
  }

  //
}
