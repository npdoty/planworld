ActiveSupport::CoreExtensions::Time::Conversions::DATE_FORMATS.merge!(
  :pw_date_time => lambda { |time|
    if time.today?
      ["Today", time.strftime("%I:%M%p").downcase] * ", "
    elsif time.yesterday?
      ["Yesterday", time.strftime("%I:%M%p").downcase] * ", "
    else
      time.strftime("%m-%d-%y %I:%M%p").downcase.gsub(/^0/, '')
    end
  },
  :planwatch => lambda { |time|
    if time.today?
      time.to_s(:time).downcase.gsub(/^0/, '')
    else
      time.strftime("%m/%d/%y").gsub(/^0/, '')
    end
  }
)
