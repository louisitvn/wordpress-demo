                        @if (Auth::user()->getOption("backend", "setting_general") == 'yes')
							<div class="tab-pane active" id="top-general">
								<div class="row">
									<?php $count = 0; ?>
									@foreach ($settings as $name => $setting)
										@if ($setting['cat'] == 'general')
											<div class="col-md-4">
												@if ($setting['type'] == 'checkbox')
													<div class="form-group checkbox-right-switch">
												@endif
													@include('helpers.form_control', [
														'type' => $setting['type'],
														'class' => (isset($setting['class']) ? $setting['class'] : "" ),
														'name' => $name,
														'value' => $setting['value'],
														'label' => trans('messages.' . $name),
														'help_class' => 'setting',
														'options' => (isset($setting['options']) ? $setting['options'] : "" ),
														'rules' => Acelle\Model\Setting::rules(),
													])
												@if ($setting['type'] == 'checkbox')
													</div>
												@endif
											</div>
											@if ($count%3 == 2)
								</div><div class="row">
											@endif
											<?php ++$count; ?>
										@endif
									@endforeach							
								</div>
								<br />
								<div class="text-left">
									<button class="btn bg-teal"><i class="icon-check"></i> {{ trans('messages.save') }}</button>
								</div>
							</div>
						@endif