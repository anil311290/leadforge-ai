<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceRule;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::withCount('rules')->orderBy('name')->paginate(15);

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'min_value' => ['nullable', 'numeric', 'min:0'],
            'max_value' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $data['slug'] ?? \Illuminate\Support\Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        Service::create($data);
        AuditService::record(auth()->user(), 'service_created', 'Service', null, null, ['name' => $data['name']]);

        return redirect()->route('services.index')->with('success', 'Service created: '.$data['name']);
    }

    public function show(Service $service)
    {
        $service->load('rules', 'caseStudies');

        return view('services.show', compact('service'));
    }

    public function storeRule(Request $request, Service $service)
    {
        $data = $this->validate($request, [
            'type' => ['required', 'string'],
            'signal' => ['nullable', 'string'],
            'keyword' => ['nullable', 'string'],
            'weight' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $service->rules()->create($data);
        AuditService::record(auth()->user(), 'service_rule_added', 'ServiceRule', null, $data);

        return back()->with('success', 'Rule added.');
    }

    public function deleteRule(Service $service, ServiceRule $rule)
    {
        $rule->delete();
        AuditService::record(auth()->user(), 'service_rule_deleted', 'ServiceRule', $rule->id);

        return back()->with('success', 'Rule removed.');
    }
}