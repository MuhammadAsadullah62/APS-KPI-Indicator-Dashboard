@extends('layouts.app')

@section('title', 'APSACS Khanewal | User Management')

@section('main-class', 'bg-slate-50')

@section('header')
    <x-dashboard.page-header title="User Directory" subtitle="Manage Faculty & Staff Accounts">
        <x-slot name="actions">
            <button type="button" class="bg-aps-green text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-lg hover:bg-emerald-900 transition-all">
                + Add New Faculty
            </button>
        </x-slot>
    </x-dashboard.page-header>
@endsection

@section('content')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-200 transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Total Faculty</p>
                    <h3 class="text-3xl font-black text-slate-800 leading-none">124</h3>
                    <p class="text-[10px] text-emerald-600 font-bold mt-2">Active across 8 departments</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-200 transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Permissions Pending</p>
                    <h3 class="text-3xl font-black text-slate-800 leading-none">12</h3>
                    <p class="text-[10px] text-amber-600 font-bold mt-2">Requires admin approval</p>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-200 transition-all">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">New Hires</p>
                    <h3 class="text-3xl font-black text-slate-800 leading-none">05</h3>
                    <p class="text-[10px] text-slate-400 font-bold mt-2">Joined in last 30 days</p>
                </div>
                <div class="bg-aps-green p-6 rounded-3xl shadow-xl text-white">
                    <p class="text-[10px] font-bold text-emerald-300 uppercase tracking-[0.2em] mb-1">Average KPI</p>
                    <h3 class="text-3xl font-black leading-none">8.42</h3>
                    <p class="text-[10px] text-emerald-100/60 font-bold mt-2 uppercase tracking-widest leading-none">Institutional Grade</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl sm:rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden min-w-0">
                <div class="px-4 py-4 sm:px-6 sm:py-6 bg-slate-50/50 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
                    <div class="relative w-full lg:w-96">
                        <input type="text" placeholder="Search by name, email, or employee ID..." class="w-full pl-12 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-medium outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all">
                        <svg class="w-5 h-5 absolute left-4 top-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            Filter
                        </button>
                        <button type="button" class="flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50/50">
                                <th class="px-3 py-3 sm:px-5 sm:py-4 md:px-8 md:py-5">Full Name & Employee ID</th>
                                <th class="px-3 py-3 sm:px-5 sm:py-4 md:px-8 md:py-5">Department</th>
                                <th class="px-3 py-3 sm:px-5 sm:py-4 md:px-8 md:py-5">System Role</th>
                                <th class="px-3 py-3 sm:px-5 sm:py-4 md:px-8 md:py-5">Access Level</th>
                                <th class="px-8 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="group hover:bg-emerald-50/30 transition-colors cursor-default">
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex-shrink-0 overflow-hidden border border-slate-200">
                                            <img src="https://ui-avatars.com/api/?name=Robert+Fox&background=064e3b&color=fff" alt="User">
                                        </div>
                                        <div>
                                            <p class="text-sm font-extrabold text-slate-900 group-hover:text-aps-green transition-colors">Robert Fox</p>
                                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-tight">APS-KHN-2023-01</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <p class="text-xs font-bold text-slate-600">Mathematics & Physics</p>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black rounded-lg uppercase tracking-widest">Faculty Member</span>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                        <span class="text-xs font-bold text-slate-700">Standard Access</span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" class="p-2 text-slate-400 hover:text-aps-green hover:bg-white rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                        <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-white rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="group hover:bg-emerald-50/30 transition-colors cursor-default">
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex-shrink-0 overflow-hidden border border-slate-200">
                                            <img src="https://ui-avatars.com/api/?name=Esther+Howard&background=064e3b&color=fff" alt="User">
                                        </div>
                                        <div>
                                            <p class="text-sm font-extrabold text-slate-900 group-hover:text-aps-green transition-colors">Esther Howard</p>
                                            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-tight">APS-KHN-2023-44</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <p class="text-xs font-bold text-slate-600">Administrative Support</p>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <span class="px-3 py-1 bg-aps-green text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Administrator</span>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-aps-green rounded-full shadow-lg"></div>
                                        <span class="text-xs font-bold text-slate-700">Elevated Access</span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 sm:px-5 sm:py-5 md:px-8 md:py-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" class="p-2 text-slate-400 hover:text-aps-green hover:bg-white rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                                        <button type="button" class="p-2 text-slate-400 hover:text-red-600 hover:bg-white rounded-xl transition-all"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-6 bg-white border-t border-slate-100 flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">Showing <span class="text-slate-900">1-10</span> of <span class="text-slate-900">124</span> users</p>
                    <div class="flex items-center gap-2">
                        <button type="button" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-400 cursor-not-allowed">Previous</button>
                        <button type="button" class="px-4 py-2 bg-aps-green text-white border border-aps-green rounded-xl text-xs font-black shadow-md hover:bg-emerald-900 transition-all">Next Page</button>
                    </div>
                </div>
            </div>

            <div class="h-8"></div>
@endsection
