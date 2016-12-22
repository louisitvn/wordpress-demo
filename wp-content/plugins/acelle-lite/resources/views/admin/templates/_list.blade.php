                        @if ($templates->count() > 0)
							<table class="table table-box pml-table"
                                current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                            >
								@foreach ($templates as $key => $item)
									<tr>
										<td width="1%">
											<div class="text-nowrap">
												<div class="checkbox inline">
													<label>
														<input type="checkbox" class="node styled"
															custom-order="{{ $item->custom_order }}"
															name="ids[]"
															value="{{ $item->uid }}"
														/>
													</label>
												</div>
												@if (request()->sort_order == 'custom_order' && empty(request()->keyword))
													<i data-action="move" class="icon icon-more2 list-drag-button"></i>
												@endif
											</div>
										</td>
										<td width="1%">
											<a href="#"  onclick="popupwindow('{{ action('TemplateController@preview', $item->uid) }}', '{{ $item->name }}', 800, 800)">
												<img width="100" height="120" src="{{ action('TemplateController@image', $item->uid) }}?v={{ rand(0,10) }}" />
											</a>
										</td>
										<td>
											<h5 class="no-margin text-bold">
												<a class="kq_search" href="#" onclick="popupwindow('{{ action('TemplateController@preview', $item->uid) }}', '{{ $item->name }}', 800, 800)">
													{{ $item->name }}
												</a>
											</h5>
											<span class="text-muted"><i class="icon-user"></i> {{ $item->user->getWPUser()->display_name }}</span>
											<br />
											<span class="text-muted">{{ trans('messages.created_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
										</td>
										<td>
											<div class="single-stat-box pull-left">
												<span class="no-margin stat-num">{{ trans('messages.template_type_' . $item->source) }}</span>
												<br>
												<span class="text-muted text-nowrap">{{ trans('messages.type') }}</span>
											</div>
										</td>
										<td class="text-right">
											<span class="text-muted2 list-status pull-left">
												<span class="label label-flat bg-{{ $item->status }}">{{ $item->status }}</span>
											</span>
											@can('preview', $item)
												<a href="#preview" class="btn bg-teal-600 btn-icon" onclick="popupwindow('{{ action('TemplateController@preview', $item->uid) }}', '{{ $item->name }}', 800, 800)"><i class="icon-zoomin3"></i> {{ trans("messages.preview") }}</a>
											@endcan
											@if (
												Auth::user()->getOption("backend", "template_update") == 'all'
												|| (Auth::user()->getOption("backend", "template_update") == 'own' && $item->user_id == Auth::user()->id)
											)
												@if ($item->source == 'builder')
													<a href="{{ action('Admin\TemplateController@rebuild', $item->uid) }}" type="button" class="btn bg-grey btn-icon"> <i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
												@else
													<a href="{{ action('Admin\TemplateController@edit', $item->uid) }}" type="button" class="btn bg-grey btn-icon"> <i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
												@endif
											@endif
											@if (
												Auth::user()->getOption("backend", "template_delete") == 'all'
												|| (Auth::user()->getOption("backend", "template_delete") == 'own' && $item->user_id == Auth::user()->id)
											)
												<div class="btn-group">										
													<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
													<ul class="dropdown-menu dropdown-menu-right">														
														<li><a delete-confirm="{{ trans('messages.delete_templates_confirm') }}" href="{{ action('Admin\TemplateController@delete', ["uids" => $item->uid]) }}"><i class="icon-trash"></i> {{ trans("messages.delete") }}</a></li>
													</ul>
												</div>
											@endif
										</td>
									</tr>
								@endforeach
							</table>
                            @include('elements/_per_page_select', ["items" => $templates])
							{{ $templates->links() }}
						@elseif (!empty(request()->keyword))
							<div class="empty-list">
								<i class="icon-magazine"></i>
								<span class="line-1">
									{{ trans('messages.no_search_result') }}
								</span>
							</div>
						@else					
							<div class="empty-list">
								<i class="icon-magazine"></i>
								<span class="line-1">
									{{ trans('messages.template_empty_line_1') }}
								</span>
							</div>
						@endif