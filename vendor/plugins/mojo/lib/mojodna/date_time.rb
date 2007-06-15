class Time
  def today?
    to_date == Date.today
  end

  def yesterday?
    to_date == Date.yesterday
  end
end

class Date
  def today?
    self == Date.today
  end

  def yesterday?
    self == Date.yesterday
  end
end