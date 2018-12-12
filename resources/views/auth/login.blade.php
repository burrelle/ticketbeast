@extends('layouts.master') @section('body')
<div class="bg-grey-light">
    <div class="min-h-screen flex justify-center items-center">
        <div class="max-w-sm flex-1">
            <form class="bg-white border-white rounded-sm overflow-hidden p-8" action="/login" method="POST">
                {{ csrf_field() }}
                <h1 class="text-xl font-light text-center mb-6">
                    Log in to your account
                </h1>
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-semibold absolute opacity-0"
                        >Email address</label
                    >
                    <div class="flex border-white rounded-sm bg-white overflow-hidden">
                        <input
                            type="email"
                            name="email"
                            class="block w-full px-2 py-2 bg-white border border-grey-light text-grey-dark"
                            placeholder="Email address"
                            value="{{ old('email') }}"
                        />
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block mb-1 text-sm font-semibold absolute opacity-0">Password</label>
                    <div class="flex border-white rounded-sm bg-white overflow-hidden">
                        <input
                            type="password"
                            name="password"
                            class="block w-full px-2 py-2 bg-white border border-grey-light text-grey-dark"
                            placeholder="Password"
                        />
                    </div>
                </div>
                <button type="submit" class="inline-block px-1 py-2 rounded-sm block w-full text-center bg-blue text-white font-bold border-blue shadow-sm">
                    Log in
                </button>
                @if($errors->any())
                <p class="text-center text-red mt-2">
                    These credentials do not match our records.
                </p>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
