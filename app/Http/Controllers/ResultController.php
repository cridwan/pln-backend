<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exports\Guest\ConsMatExport;
use App\Exports\Guest\ManpowerExport;
use App\Exports\Guest\PartExport;
use App\Exports\Guest\ScopeStandartExport;
use App\Http\Controllers\Controller;
use App\Http\Middleware\ResponseMiddleware;
use App\Models\InspectionType;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Spatie\RouteDiscovery\Attributes\Route;

#[Route(middleware: ResponseMiddleware::class)]
#[Group('Guest Result Resource')]
class ResultController extends Controller
{
    /**
     * download recap consmat
     */
    #[Route(method: 'get', uri: 'export/consmat')]
    public function exportConsMat(Request $request)
    {
        if ($request->isNotFilled('inspection_type_uuid')) {
            throw new BadRequestException('InspectionType tidak terpilih');
        }

        $inspectionType = InspectionType::find($request->inspection_type_uuid);

        if (!$inspectionType) {
            throw new BadRequestException('Inspection type tidak ditemukan');
        }

        $inspectionType->loadMissing(['machine']);
        $filename = date('YmdHis') . '-consumable-material.xlsx';
        return (new ConsMatExport($inspectionType))->execute();
    }

    /**
     * download recap part
     */
    #[Route(method: 'get', uri: 'export/part')]
    public function exportPart(Request $request)
    {
        if ($request->isNotFilled('inspection_type_uuid')) {
            throw new BadRequestException('Inspection type tidak terpilih');
        }

        $inspectionType = InspectionType::find($request->inspection_type_uuid);

        if (!$inspectionType) {
            throw new BadRequestException('InspectionType tidak ditemukan');
        }

        $inspectionType->loadMissing(['machine']);
        return (new PartExport($inspectionType))->execute();
    }

    /**
     * download recap manpower
     */
    #[Route(method: 'get', uri: 'export/manpower')]
    public function exportManpower(Request $request)
    {
        if ($request->isNotFilled('inspection_type_uuid')) {
            throw new BadRequestException('Inspection type tidak terpilih');
        }

        $inspectionType = InspectionType::find($request->inspection_type_uuid);

        if (!$inspectionType) {
            throw new BadRequestException('Inspection type tidak ditemukan');
        }

        $inspectionType->loadMissing(['machine']);
        return (new ManpowerExport($inspectionType))->execute();
    }

    /**
     * download recap scope standart
     */
    #[Route(method: 'get', uri: 'export/scope-standart')]
    public function exportScopeStandart(Request $request)
    {
        if ($request->isNotFilled('inspection_type_uuid')) {
            throw new BadRequestException('Inspection type tidak terpilih');
        }

        $inspectionType = InspectionType::find($request->inspection_type_uuid);

        if (!$inspectionType) {
            throw new BadRequestException('Inspection Type tidak ditemukan');
        }

        $inspectionType->loadMissing(['machine']);

        return (new ScopeStandartExport($inspectionType))->execute();
    }
}
