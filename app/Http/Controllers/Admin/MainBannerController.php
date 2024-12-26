<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MainBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MainBannerController extends Controller
{
    public function index()
    {
        $mainBanners = MainBanner::all();
        return view('admin.main_banners.index', compact('mainBanners'));
    }

    public function create()
    {
        return view('admin.main_banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $uploadedImages[] = $image->store('main_banners', 'public');
            }
        }

        $image1Path = $request->file('image1') ? $request->file('image1')->store('main_banners', 'public') : null;
        $image2Path = $request->file('image2') ? $request->file('image2')->store('main_banners', 'public') : null;

        MainBanner::create([
            'images' => $uploadedImages,
            'image1' => $image1Path,
            'image2' => $image2Path,
        ]);

        return redirect()->route('main_banners.index')->with('success', 'Banner muvaffaqiyatli qo‘shildi!');
    }
    public function edit(MainBanner $mainBanner)
    {
        return view('admin.main_banners.edit', compact('mainBanner'));
    }

    public function update(Request $request, MainBanner $mainBanner)
    {
        // Validate incoming request
        $request->validate([
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Initialize an array to hold the updated images
        $updatedImages = $mainBanner->images ? $mainBanner->images : [];

        // Handle the uploaded images
        if ($request->has('images')) {
            foreach ($request->file('images') as $image) {
                $updatedImages[] = $image->store('main_banners', 'public');
            }
        }

        // Handle image deletions
        if ($request->has('delete_images')) {
            foreach ($request->delete_images as $deleteImage) {
                if (($key = array_search($deleteImage, $updatedImages)) !== false) {
                    unset($updatedImages[$key]); // Remove from array
                    Storage::disk('public')->delete($deleteImage); // Delete from storage
                }
            }
        }

        // Handle image1 upload or deletion
        $image1Path = $request->file('image1')
            ? $request->file('image1')->store('main_banners', 'public')
            : $mainBanner->image1;

        if ($request->hasFile('image1') && $mainBanner->image1) {
            Storage::disk('public')->delete($mainBanner->image1); // Delete old image1 if new one is uploaded
        }

        // Handle image2 upload or deletion
        $image2Path = $request->file('image2')
            ? $request->file('image2')->store('main_banners', 'public')
            : $mainBanner->image2;

        if ($request->hasFile('image2') && $mainBanner->image2) {
            Storage::disk('public')->delete($mainBanner->image2); // Delete old image2 if new one is uploaded
        }

        // Update the mainBanner with the new images
        $mainBanner->update([
            'images' => array_values($updatedImages), // Update the images array
            'image1' => $image1Path, // Update image1
            'image2' => $image2Path, // Update image2
        ]);

        // Redirect with success message
        return redirect()->route('main_banners.index')->with('success', 'Banner muvaffaqiyatli yangilandi!');
    }



    public function destroy(MainBanner $mainBanner)
    {
        if ($mainBanner->images) {
            foreach ($mainBanner->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
        if ($mainBanner->image1) {
            Storage::disk('public')->delete($mainBanner->image1);
        }
        if ($mainBanner->image2) {
            Storage::disk('public')->delete($mainBanner->image2);
        }

        $mainBanner->delete();
        return redirect()->route('main_banners.index')->with('success', 'Banner o‘chirildi!');
    }
}

