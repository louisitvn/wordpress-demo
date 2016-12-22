                        @if ($campaigns->count() > 0)
							<table class="table table-box pml-table"
                                current-page="{{ empty(request()->page) ? 1 : empty(request()->page) }}"
                            >
								@foreach ($campaigns as $key => $item)
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
										<td>
											<h5 class="no-margin text-bold">
												<a class="kq_search" href="{{ action('CampaignController@show', $item->uid) }}">
													{{ $item->name }}
												</a>
											</h5>
											<span class="text-muted">{{ trans('messages.' . $item->type) }}</span>
												
											@if ($item->subscribers()->count())
												<br />
												@if (is_object($item->segment))
													<span class="text-semibold">{{ $item->subscribers()->count() }}</span> <span class="text-muted">{{ strtolower(trans('messages.subscribers')) }} ({{ $item->mailList->name }} . {{ $item->segment->name }})</span>
												@elseif (is_object($item->mailList))
													<span class="text-semibold">{{ $item->subscribers()->count() }}</span> <span class="text-muted">{{ strtolower(trans('messages.subscribers')) }} ({{ $item->mailList->name }})</span>
												@endif
											@endif
											
											<br />
											@if ($item->status != 'new')
												<span class="text-muted2">{{ trans('messages.run_at') }}: &nbsp;&nbsp;<i class="icon-alarm mr-0"></i> {{ isset($item->run_at) ? Tool::formatDateTime($item->run_at) : "" }}</span>
											@else
												<span class="text-muted2">{{ trans('messages.updated_at') }}: {{ Tool::formatDateTime($item->created_at) }}</span>
											@endif
											
											
										</td>
										<!--<td>
											<div class="single-stat-box pull-left">
												@if (is_object($item->segment))
													<span class="no-margin stat-num">{{ $item->mailList->name }} . {{ $item->segment->name }}</span>
													<br>
													<span class="text-muted text-nowrap">{{ $item->subscribers()->count() }} {{ strtolower(trans('messages.subscribers')) }}</span>
												@elseif (is_object($item->mailList))
													<span class="no-margin stat-num">{{ $item->mailList->name }}</span>
													<br>
													<span class="text-muted text-nowrap">{{ $item->subscribers()->count() }} {{ strtolower(trans('messages.subscribers')) }}</span>
												@endif
											</div>
										</td>-->
										<td class="stat-fix-size-sm">
											@if ($item->status != 'new')
												<div class="single-stat-box pull-left ml-20">
													<span class="no-margin text-teal-800 stat-num">{{ $item->deliveredRate() }}%</span>
													<div class="progress progress-xxs bg-danger">
														<div class="progress-bar progress-bar-info" style="width: {{ $item->deliveredRate() }}%">
														</div>
													</div>
													<span class="text-semibold">{{ $item->deliveredCount() }} / {{ $item->subscribers()->count() }}</span>
													<br />
													<span class="text-muted">{{ trans('messages.sent') }}</span>
												</div>
												<div class="single-stat-box pull-left ml-20">
													<span class="no-margin text-teal-800 stat-num">{{ $item->openUniqRate() }}%</span>
													<div class="progress progress-xxs">
														<div class="progress-bar progress-bar-info" style="width: {{ $item->openUniqRate() }}%">
														</div>
													</div>
													<span class="text-muted">{{ trans('messages.open_rate') }}</span>
												</div>
												<div class="single-stat-box pull-left ml-20">
													<span class="no-margin text-teal-800 stat-num">{{ $item->clickedEmailsRate() }}%</span>
													<div class="progress progress-xxs">
														<div class="progress-bar progress-bar-info" style="width: {{ $item->clickedEmailsRate() }}%">
														</div>
													</div>
													<span class="text-muted">{{ trans('messages.click_rate') }}</span>
												</div>
											@endif
										</td>										
										<td class="text-right">
											<span class="text-muted2 list-status pull-left">
												<span class="label label-flat bg-{{ $item->status }}">{{ $item->status }}</span>
											</span>											
											@if (\Gate::allows('update', $item))
												<a href="{{ action('CampaignController@edit', $item->uid) }}" type="button" class="btn bg-grey btn-icon"> <i class="icon-pencil"></i> {{ trans('messages.edit') }}</a>
											@endif
											@if (\Gate::allows('overview', $item))
												<a href="{{ action('CampaignController@overview', $item->uid) }}" data-popup="tooltip" title="{{ trans('messages.overview') }}" type="button" class="btn bg-teal-600 btn-icon"><i class="icon-stats-growth"></i> {{ trans('messages.overview') }}</a>
											@endif
											@if (\Gate::allows('delete', $item) || \Gate::allows('pause', $item) || \Gate::allows('restart', $item))
												<div class="btn-group">										
													<button type="button" class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret ml-0"></span></button>
													<ul class="dropdown-menu dropdown-menu-right">
														@if (\Gate::allows('pause', $item))
															<li><a link-confirm="{{ trans('messages.pause_campaigns_confirm') }}" href="{{ action('CampaignController@pause', ["uids" => $item->uid]) }}"><i class="icon-pause"></i> {{ trans("messages.pause") }}</a></li>
														@endif
														@if (\Gate::allows('restart', $item))
															<li><a link-confirm="{{ trans('messages.restart_campaigns_confirm') }}" href="{{ action('CampaignController@restart', ["uids" => $item->uid]) }}"><i class="icon-history"></i> {{ trans("messages.restart") }}</a></li>
														@endif
														@if (\Gate::allows('copy', $item))
															<li>														
																<a data-uid="{{ $item->uid }}" data-name="{{ trans("messages.copy_of_campaign", ['name' => $item->name]) }}" class="copy-campaign-link">
																	<i class="icon-copy4"></i> {{ trans('messages.copy') }}
																</a>
															</li>
														@endif
														@if (\Gate::allows('delete', $item))
															<li><a delete-confirm="{{ trans('messages.delete_campaigns_confirm') }}" href="{{ action('CampaignController@delete', ["uids" => $item->uid]) }}"><i class="icon-trash"></i> {{ trans("messages.delete") }}</a></li>
														@endif
													</ul>
												</div>
											@endif
										</td>
									</tr>
								@endforeach
							</table>
                            @include('elements/_per_page_select', ["items" => $campaigns])
							{{ $campaigns->links() }}
						@elseif (!empty(request()->keyword))
							<div class="empty-list">
								<i class="icon-paperplane"></i>
								<span class="line-1">
									{{ trans('messages.no_search_result') }}
								</span>
							</div>
						@else					
							<div class="empty-list">
								<i class="icon-paperplane"></i>
								<span class="line-1">
									{{ trans('messages.campaign_empty_line_1') }}
								</span>
							</div>
						@endif