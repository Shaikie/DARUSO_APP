<?php
namespace App\Http\Requests;
use App\Enums\PermissionName;
use Illuminate\Foundation\Http\FormRequest;
class StorePostRequest extends FormRequest {
 public function authorize(): bool { return $this->user()?->hasPermission(PermissionName::PostCreate->value) ?? false; }
 public function rules(): array { return [
  'title'=>['required','string','min:5','max:255'],'excerpt'=>['nullable','string','max:500'],
  'content'=>['required','string','min:10'],'cover_image_url'=>['nullable','url','max:2048'],
  'published_at'=>['nullable','date'],'is_published'=>['nullable','boolean'],
 ]; }
}