<div>
    <form wire:submit.prevent="save">
        <div class="lg:flex gap-2">
            <div class="w-full lg:w-1/2 p-3">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" wire:model="name"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" wire:model="description" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                    @error('description') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Categories</label>
                    @foreach($allCategories as $category)
                        <label class="inline-flex items-center mt-3">
                            <input type="checkbox" wire:model="categories" value="{{ $category->id }}"
                                   class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2 text-gray-700">{{ $category->name }}</span>
                        </label>
                    @endforeach
                    @error('categories') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="validFrom" class="block text-sm font-medium text-gray-700">Valid From</label>
                    <input type="date" id="validFrom" wire:model="validFrom"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('validFrom') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="validTo" class="block text-sm font-medium text-gray-700">Valid To</label>
                    <input type="date" id="validTo" wire:model="validTo"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('validTo') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="displayInBreaches"
                               class="form-checkbox h-5 w-5 text-blue-600">
                        <span class="ml-2 text-gray-700">Display in breaches list</span>
                    </label>
                </div>

                <div class="mb-4">
                    <label for="geoJSONFile" class="block text-sm font-medium text-gray-700">Upload GeoJSON File</label>
                    <div
                        x-data="{
                            isHovering: false,
                            fileName: @entangle('fileName').defer
                        }"
                        x-on:dragover.prevent="isHovering = true"
                        x-on:dragleave.prevent="isHovering = false"
                        x-on:drop.prevent="
                            isHovering = false;
                            $refs.fileInput.files = $event.dataTransfer.files;
                            fileName = $event.dataTransfer.files[0].name;
                            $refs.fileInput.dispatchEvent(new Event('change'));
                        "
                        :class="{
                            'bg-blue-100 border-blue-300': isHovering,
                            'bg-green-100 border-green-300': fileName
                        }"
                        class="mt-1 flex flex-col items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition duration-150 ease-in-out">
                        <div class="text-center">
                            <div x-show="!fileName" class="text-6xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div x-show="fileName" class="text-6xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mx-auto">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                </svg>
                            </div>
                            <div class="flex flex-col items-center text-sm text-gray-600">
                                <label for="geoJSONFile"
                                       class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span x-text="fileName ? 'Change file' : 'Select a file'"></span>
                                    <input
                                        x-ref="fileInput"
                                        id="geoJSONFile"
                                        wire:model="geoJSONFile"
                                        type="file"
                                        class="sr-only"
                                        accept=".json,.geojson"
                                        x-on:change="fileName = $event.target.files[0].name"
                                    >
                                </label>
                                <p class="mt-1" x-show="!fileName">or drag and drop</p>
                                <p class="mt-1" x-show="fileName" x-text="fileName"></p>
                            </div>
                            <p class="text-xs text-gray-500 mt-2" x-show="!fileName">
                                JSON or GeoJSON up to 10MB
                            </p>
                        </div>
                    </div>
                    @error('geoJSONFile') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="w-full lg:w-1/2 p-3">
                <input type="hidden" wire:model="drawnGeoJSON" id="drawnGeoJSON">

                <div wire:ignore id="map-container">
                    <label class="block text-sm font-medium text-gray-700">Draw Area on Map</label>
                    <div id="map" class="h-96 w-full border border-gray-300 rounded-md"></div>
                </div>

                @error('drawnGeoJSON') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>


        <div class="mt-4">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                {{ $areaId ? 'Update Area' : 'Create Area ' }}
            </button>
        </div>
    </form>
</div>

