<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePdfsRequest;
use App\Http\Resources\PdfProcessResource;
use App\Models\PdfProcess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PdfProcessPageController extends Controller
{
    // TODO: here the user will be able to get all pdf pages of the process after the uploaded pdf are processed
}
