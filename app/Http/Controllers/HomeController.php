<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Jobs\ResizeImage;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Models\AnnouncementImage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Jobs\GoogleVisionRemoveFaces;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GoogleVisionSafeSearchImage;
use App\Jobs\GoogleVisionLabelImage;
use App\Http\Requests\AnnouncementRequest;


class HomeController extends Controller
{
    public function newAnnouncement (Request $request)
    {
        $uniqueSecret = $request->old(
            'uniqueSecret',
            base_convert(sha1(uniqid(mt_rand())),16,36)
        );

        return view('announcements.new', compact('uniqueSecret'));
    }

    public function createAnnouncement(AnnouncementRequest $request)
    {
        $a = new Announcement();
        $a->name = $request->input('name');
        $a->description = $request->input('description');
        $a->price = $request->input('price');
        $a->user_id = Auth::id();
        $a->category_id = $request->input('category');
        $a->save();

        $uniqueSecret = $request->input('uniqueSecret');

        $images = session()->get("images.{$uniqueSecret}",[]);
        $removedImages = session()->get("removedImages.{$uniqueSecret}",[]);

        $images = array_diff($images, $removedImages);
        var_dump($images);

        foreach($images as $image){
            $i = new AnnouncementImage;
            $fileName = basename($image);
            $newFilePath = "public/announcements/{$a->id}/{$fileName}";
            Storage::move($image,$newFilePath);

/*             dispatch(new ResizeImage($newFilePath,300,380));
            dispatch(new ResizeImage($newFilePath,300,300));
            dispatch(new ResizeImage($newFilePath,500,400)); */

            $i->file = $newFilePath;
            $i->announcement_id = $a->id;
            $i->save();
/*             dispatch(new GoogleVisionSafeSearchImage($i->id));
 */
            Bus::chain([
                new ResizeImage($newFilePath,300,380),
                new ResizeImage($newFilePath,500,400),
                new ResizeImage($newFilePath,300,300),
                new GoogleVisionSafeSearchImage($i->id),
                new GoogleVisionLabelImage($i->id),
                new GoogleVisionRemoveFaces($i->id),
            ])->dispatch();

        }

        File::deleteDirectory(storage_path("app/public/temp/{$uniqueSecret}"));

        return redirect()->route('home')->with('announcement.create.success','Anuncio creado con exito. Será revisado en la mayor brevedad posible.');
    }



    public function detailAnnouncement($id)
    {
        $announcement = Announcement::findOrFail($id);
        return view('announcements.detail', compact('announcement'));
    }


    public function uploadImages(Request $request)
    {

        $uniqueSecret = $request->input('uniqueSecret');
        $filePath = $request->file('file')->store("public/temp/{$uniqueSecret}");

        dispatch(new ResizeImage($filePath,120,120));

        session()->push("images.{$uniqueSecret}", $filePath);
        return response()->json(
            [
                'id' => $filePath
            ]
        );
    }


    public function removeImages(Request $request)
    {
        $uniqueSecret = $request->input('uniqueSecret');
        $fileName = $request->input('id');
        session()->push("removedImages.{$uniqueSecret}", $fileName);
        Storage::delete($fileName);
        return response()->json('ok');

    }

    public function getImages(Request $request)
    {
        $uniqueSecret = $request->input('uniqueSecret');
        $images = session()->get("images.{$uniqueSecret}",[]);
        $removedImages = session()->get("removedImages.{$uniqueSecret}",[]);
        $images = array_diff($images, $removedImages);

        $data = [];

        foreach($images as $image){
            $data[] = [
                'id' => $image,
                'src' => AnnouncementImage::getUrlByFilePath($image,120,120),
                'name' => basename($image),
                'size' => Storage::size($image)
            ];
        }

        return response()->json($data);
    }
}
